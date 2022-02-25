<?php

namespace App\Pipelines;

use Closure;

class FilterByStatus implements Pipe
{
    public function handle($content, Closure $next)
    {
        // Here you perform the task and return the updated $content
        // to the next pipe
        return  $next($content)->when(
            request('status'),
            fn($query) => $query->where('status', request('status') )
        );

    }

}
