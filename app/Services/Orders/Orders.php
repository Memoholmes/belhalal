<?php

namespace App\Services\Orders;

use App\Yantrana\Components\CreditPackage\Models\CreditPackageModel;
use App\Services\Integrations\Payment\Constants\PaymentProviders;
use App\Services\Integrations\Payment\Constants\PaymentStatus;
use App\Order;
use Ramsey\Uuid\Uuid;

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
        // dd($orderUid, $transactionId);
    }

    public function OrderPaymentFailure(string $orderUid)
    {
        // dd($orderUid);
    }
}