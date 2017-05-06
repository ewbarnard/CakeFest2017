<?php
namespace App\GenerateToken;

class GenerateToken {

   public static function token() {
      return substr(str_replace(
         ['+', '/'],
         ['-', '_'],
         base64_encode(random_bytes(16))
      ), 0, 22);
   }
}
