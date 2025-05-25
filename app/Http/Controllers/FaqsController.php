<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;

class FaqsController extends Controller
{
    const CONTROLLER_NAME = "Faqs Controller";

    /**
     * Get all faqs detail
     *
     * @group Role api
     * @param Request $request
     * @authenticated
     * @return Json response
     */
    public function getFaqs(Request $request)
    {
        try {
            $response = Faq::get();
            return $this->actionSuccess('Faqs get SuccessFully', $response);
        } catch (\Exception $e) {
            createExceptionError($e, self::CONTROLLER_NAME, __FUNCTION__);
            return $this->actionFailure($e->getMessage());
        }
        return $this->actionFailure('Faqs List Not Found!');
    }

  
}
