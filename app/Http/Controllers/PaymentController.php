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
    public function paymentProcessSucess(int $orderId, Request $request)
    {
        $order = Order::findOrFail($orderId);
        $this->hesabe->paymentProcess($order, $request->data);
        dd($request->all());
    }
    public function paymentProcessFailure(int $orderId, Request $request)
    {
        $order = Order::findOrFail($orderId);
        $this->hesabe->paymentProcess($order, $request->data);
        dd($request->all());
    }

    public function paymentProcessWebhook(int $orderId, Request $request)
    {
        dd($request->all(), $orderId);
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
}
