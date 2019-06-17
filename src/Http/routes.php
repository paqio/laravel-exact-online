<?php

Route::group(['prefix' => 'exact', 'middleware' => config('laravel-exact-online.exact_multi_user') ? ['web','auth'] : ['web'] ], function() {
   
});
