<?php

namespace DWPL\RequestRouter;

if ( !class_exists('Router') ) {
    class Router implements Handler {
        protected ?Handler $DefaultRoute = null;
        protected array $routes = [];

        public function handle(array $path) : void {
            $this->handlePath($this->routes, $path, 0);
        }

        public function addRoute(array $path, $handler) {
            $routes = &$this->routes;
            for($i = 0; $i < count($path)-1; $i++) {
                $part = $path[$i];
                $routes[$part] = $routes[$part] ?? [];
                $routes = &$routes[$part];
            }

            $routes[end($path)] = $handler;
        }

        public function setRoutes(array $routes) {
            $this->routes = $routes;
        }

        public function setDefaultHandler(Handler $Handler) {
            $this->DefaultRoute = $Handler;
        }

        protected function handlePath(array $routes, array $path, int $depth) {
            $part = $path[$depth] ?? null;
            if ($part === null) {
                $this->handleDefault(array_slice($path, $depth));
                return;
            }

            $handler = $routes[$part] ?? null;
            if ($handler === null) {
                $this->handleDefault(array_slice($path, $depth));
                return;
            }

            if ($handler instanceof Handler) {
                $handler->handle(array_slice($path, $depth+1));
                return;
            }

            if (is_callable($handler)) {
                call_user_func($handler, array_slice($path, $depth));
                return;
            }

            $this->handlePath($handler, $path, $depth+1);
        }

        protected function handleDefault(array $path) {
            if ($this->DefaultRoute === null) {
                return;
            }

            $this->DefaultRoute->handle($path);
        }

        public static function splitURI(?string $uri = null) : array {
            if ($uri === null) {
                $uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
            }

            return explode("/", ltrim($uri, "/"));
        }
    }
}
