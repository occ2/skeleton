<?php
/*
 * The MIT License
 *
 * Copyright 2018 Milan Onderka <milan_onderka@occ2.cz>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace app\Base\factories;

/**
 * Router factory
 *
 * @author Milan Onderka
 * @version 1.1.0
 */
class RouterFactory
{
    use \Nette\StaticClass;

    const DEFAULT_MODULE="User",
          DEFAULT_PRESENTER="Main",
          DEFAULT_ACTION="default",
          DEFAULT_NAME="home";

    /**
     * @return \Nette\Application\IRouter
     */
    public static function createRouter($languages=[],$modules=[])
    {
        $router = new \Nette\Application\Routers\RouteList;
        $lang = isset($languages["supported"]) ? "<locale=" . $languages["default"]  . " " . implode("|", $languages["supported"]) . ">":"";
        $router[] = new \Nette\Application\Routers\Route(
            $lang,
            [
                "module"=>static::DEFAULT_MODULE,
                "presenter"=>static::DEFAULT_PRESENTER,
                "action"=>static::DEFAULT_ACTION,
                "name"=>static::DEFAULT_NAME
            ]
        );
        foreach ($modules as $key=>$value){


            $router[] = new \Nette\Application\Routers\Route(
                $lang . '/' . $key,
                [
                    "module"=>$value,
                    "presenter"=>static::DEFAULT_PRESENTER,
                    "action"=>static::DEFAULT_ACTION
                ]
            );

            $router[] = new \Nette\Application\Routers\Route(
                $lang . '/' . $key,
                [
                    'module'    => $value,
                    'presenter' => static::DEFAULT_PRESENTER,
                    'action'    => static::DEFAULT_ACTION,
                    'id'        => null
                ]


            );

            $router[] = new \Nette\Application\Routers\Route(
                $lang . '/' . $key . '/<presenter>/<action>[/<id>]',
                [
                    'module'    => $value,
                    'presenter' => static::DEFAULT_PRESENTER,
                    'action'    => static::DEFAULT_ACTION,
                    'id'        => null
                ]

            );
        }
        return $router;
    }
}
