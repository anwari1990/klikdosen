<?php

namespace Mild\Handlers;

class ValidationHandler
{
    /**
     * A list of the inputs that are never flashed.
     *
     * @var array
     */
    protected $dontFlash = [];

    /**
     * @param \Throwable $e
     * @param \Mild\Http\Request|null $request
     * @return \Mild\Http\RedirectResponse
     * @throws \ReflectionException
     */
    public function handle($e, $request = null)
    {
        $messageBag = $e->getValidator()->getMessageBag();
        if ($request->isXhr() || $request->isJson()) {
            return response()->json($messageBag->all());
        }
        return redirect()->back()->withErrors($messageBag)->withInputs($this->dontFlash);
    }
}
