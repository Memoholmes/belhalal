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
        return url('payment-process-success/' . $this->id);
    }

    public function failureUrl()
    {
        return url('payment-process-failure/' . $this->id);
    }

    public function webhookUrl()
    {
        return "https://api.webhookinbox.com/i/3OIQaeR7/in/";
        return url('payment-process-webhook/' . $this->id);
    }
}
