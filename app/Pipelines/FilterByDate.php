<?php

namespace App\Pipelines;

use Closure;

class FilterByDate implements Pipe
{
    public function handle($content, Closure $next)
    {
        // Here you perform the task and return the updated $content
        // to the next pipe
        return  $next($content)->when(
                request('from_date') && request('to_date'),
                fn($query) => $query->betweenDates(request('from_date'), request('to_date'))
        );

    }

}
