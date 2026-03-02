<?php

namespace App\Jobs;

abstract class Queue
{
    const QUEUE_VERY_HIGH = 'very-high';
    const QUEUE_HIGH = 'high';
    const QUEUE_DEFAULT = 'default';
    const QUEUE_LOW = 'low';
    const QUEUE_VERY_LOW = 'very-low';
    const QUEUE_PURCHASE_ORDERS = 'purchase-orders';
    const QUEUE_SALES_ORDERS = 'sales-orders';
}
