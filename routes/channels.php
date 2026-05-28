<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('orders', fn () => true);
