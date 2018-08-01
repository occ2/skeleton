<?php
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
