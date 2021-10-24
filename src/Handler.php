<?php

namespace DWPL\RequestRouter;

if ( !interface_exists( 'Handler' ) ) {
    interface Handler {
        public function handle(array $path) : void;
    }
}
