<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Yantrana\Base\BaseController;
use App\Services\Integrations\Payment\Hesabe\Hesabe;
use App\Services\Orders\Orders;
use App\Yantrana\Components\CreditPackage\Models\CreditPackageModel;
use App\Order;
use App\Services\Integrations\Payment\Hesabe\Models\HesabeCheckoutRequestModel;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Services\Integrations\Payment\Constants\PaymentStatus;

class PaymentController extends BaseController
{
    protected Hesabe $hesabe;

    public function __construct()
    {
        $this->hesabe = new Hesabe;
    }

    public function payPackageWeb($packageId)
    {
        $package = CreditPackageModel::findOrFail($packageId);
        $order = (new Orders)->makeOrder($package);
        $requestData = new HesabeCheckoutRequestModel($order);
        $paymentUrl = $this->hesabe->makePaymentUrl($requestData);
        return redirect()->to($paymentUrl);
    }
    // Handle on success redirect
    public function paymentProcessSucess(string $orderUid, Request $request)
    {
        $order = Order::where('uid', $orderUid)->firstOrFail();
        $status = $this->hesabe->paymentProcessAfterRedirect($order, $request->data, true);
        return view('payments.hesabe-success', ['uid' => $orderUid, 'status' => $status]);

    }
    // Handle on failure redirect
    public function paymentProcessFailure(string $orderUid, Request $request)
    {
        $order = Order::where('uid', $orderUid)->firstOrFail();
        $status = $this->hesabe->paymentProcessAfterRedirect($order, $request->data, false);
        return view('payments.hesabe-failure', ['uid' => $orderUid, 'status' => $status]);
    }

    // Handle webhook hit
    public function paymentProcessWebhook(string $orderUid, Request $request)
    {
        sleep(10); // Sleep till the redirect done or fail
        $status = $this->hesabe->paymentsProcessByOrderUid($orderUid);
        dump($orderUid ,$status);
    }
    
    public function payPackageApi($packageId)
    {
        $package = CreditPackageModel::findOrFail($packageId);
        $order = (new Orders)->makeOrder($package);

        $hesabeCredentials = [
            'HESABE_PAYMENT_API_URL' => env('HESABE_PAYMENT_API_URL'),
            'HESABE_ACCESS_CODE' => env('HESABE_ACCESS_CODE'),
            'HESABE_MERCHANT_SECRET_KEY' => env('HESABE_MERCHANT_SECRET_KEY'),
            'HESABE_MERCHANT_IV' => env('HESABE_MERCHANT_IV'),
            'HESABE_MERCHANT_CODE' => env('HESABE_MERCHANT_CODE'),
        ];
        $data = [
            'hesabe_credentials' => $hesabeCredentials,
            'order_uid' => $order->uid,
            'amount' => (float) $order->amount,
            'currency_code' => $order->currency_code,
            'payment_type' => "0",
            'version' => "2.0",
            'webhook_url' => $order->webhookUrl(),
        ];
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function updatePaymentStatusApi(Request $request)
    {
        $rules = [
            'order_uid' => 'required|string',
            'status' => 'required|boolean',
            'transaction_id' => 'string|required_if:status,true',
        ];
        $validate = Validator::make(request()->all(), $rules);
        if ($validate->fails()) {
            $errors = $validate->errors()->all();
            return response()->json([
                'success' => false,
                'errors' => $errors,
            ], 400);
        }
        Order::where('uid', $request->order_uid)->firstOrFail();
        // Success
        if ($request->status) {
            (new Orders)->OrderPaymentSuccess($request->order_uid, $request->transaction_id);
        }
        // Failure
        if (!$request->status) {
            (new Orders)->OrderPaymentFailure($request->order_uid);
        }

        return response()->json([
            'success' => true,
            'data' => [],
        ]);
    }

    public function checkPaymentsCronJob()
    {
        $orders = Order::whereBetween('created_at', [Carbon::now()->subHours(1), Carbon::now()->subMinutes(10)])
            ->where('after_payment_is_processing', false)
            ->where('after_payment_proccess_is_completed', false)
            ->where('payment_status', PaymentStatus::PENDING)
            ->get();
        dump($orders);
        foreach ($orders as $order) {
            $status = $this->hesabe->paymentsProcessByOrderUid($order->uid);
            dump($order->uid ,$status);
            sleep(1);
        }
    }
}
