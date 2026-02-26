<?php

namespace Look\Workflows\Laravel\Controllers;

use Look\Workflows\Core\Schemas\Traits\UsesSchemaRegistry;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SchemaIndex extends Controller
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use UsesSchemaRegistry;

    use ValidatesRequests;

    public function __invoke(Request $request)
    {
        $schemas = $this->registry()->list();

        collect($schemas);

        return $schemas;
    }
}
