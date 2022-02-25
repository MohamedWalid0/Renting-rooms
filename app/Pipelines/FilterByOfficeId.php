<?php

namespace App\Pipelines;

use Closure;

class FilterByOfficeId implements Pipe
{
    public function handle($content, Closure $next)
    {
        // Here you perform the task and return the updated $content
        // to the next pipe
        return  $next($content)->when(request('office_id'),
                fn($query) => $query->where('office_id', request('office_id'))
            );

        }
}
