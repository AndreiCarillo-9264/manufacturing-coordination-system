<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Base Controller class that all other controllers should extend.
 *
 * This provides access to common Laravel controller helpers such as:
 * - $this->authorize()           (authorization checks)
 * - $this->validate()            (request validation)
 * - $this->middleware()          (middleware registration)
 * etc.
 */
abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}