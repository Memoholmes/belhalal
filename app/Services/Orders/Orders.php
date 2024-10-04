<?php

namespace App\Services\Orders;

use App\Yantrana\Components\CreditPackage\Models\CreditPackageModel;
use App\Yantrana\Components\User\Models\CreditWalletTransaction;
use App\Services\Integrations\Payment\Constants\PaymentProviders;
use App\Services\Integrations\Payment\Constants\PaymentStatus;
use App\Order;
use Ramsey\Uuid\Uuid;
use DB;

class Orders {
	public function makeOrder(CreditPackageModel $package)
	{
        $uuid = Uuid::uuid4(); 
        return Order::create([
            'uid' => $uuid->toString(),
            'user_id' => getUserID(),
            'package_id' => $package->_id,
            'amount' => $package->price,
            'payment_status' => PaymentStatus::PENDING,
            'payment_provider' => PaymentProviders::HESABE,
            'currency_code' => "KWD",
            'after_payment_is_processing' => false,
            'after_payment_proccess_is_completed' => false,
            'after_payment_process_started_at' => null,
            'after_payment_process_completed_at' => null,
            'transaction_id' => null,
        ]);
	}

    public function OrderPaymentSuccess(string $orderUid, string $transactionId)
    {
        $order = $this->returnOrderIfValidToProcess($orderUid);
        if (empty($order)) {
            return;
        }
        $this->startOrderProcess($order);
        DB::transaction(function() use ($order, $transactionId) {
            $order->transaction_id = $transactionId;
            $order->payment_status = PaymentStatus::SUCCESS;
            $order->save();
            $this->applyUserPackage($order);
            $this->completeOrderProcess($order);
        });
    }

    public function OrderPaymentFailure(string $orderUid)
    {
        $order = $this->returnOrderIfValidToProcess($orderUid);
        if (empty($order)) {
            return;
        }
        $this->startOrderProcess($order);
        DB::transaction(function() use ($order) {
            $order->payment_status = PaymentStatus::FAILURE;
            $order->save();
            $this->completeOrderProcess($order);
        });
    }

    private function returnOrderIfValidToProcess(string $orderUid): ?Order
    {
        return Order::where('uid', $orderUid)
            ->where('after_payment_is_processing', false)
            ->where('after_payment_proccess_is_completed', false)
            ->first();
    }

    private function startOrderProcess(Order $order)
    {
        $order->after_payment_is_processing = true;
        $order->after_payment_process_started_at = now();
        $order->save();
    }

    private function completeOrderProcess(Order $order)
    {
        $order->after_payment_is_processing = false;
        $order->after_payment_proccess_is_completed = true;
        $order->after_payment_process_completed_at = now();
        $order->save();
    }

    private function applyUserPackage(Order $order)
    {
        // LOGIC HERE WILL BE CHCEKED AGAIN
        $package = CreditPackageModel::find($order->package_id);
        
        $uuid = Uuid::uuid4(); 
    
        $credit = CreditWalletTransaction::updateOrCreate(
            [
                'order_id' => $order->id
            ],
            [
                '_uid' => $uuid->toString(),
                'created_at' => now(),
                'updated_at' => now(),
                'status' => 1,
                'users__id' => $order->user_id,
                'credits' => $package->credits,
            ]
        );
    }
}