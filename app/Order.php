<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function successUrl()
    {
        return url('payment-process-success/' . $this->uid);
    }

    public function failureUrl()
    {
        return url('payment-process-failure/' . $this->uid);
    }

    public function webhookUrl()
    {
        return url('api/payment-process-webhook/' . $this->uid);
    }
}
