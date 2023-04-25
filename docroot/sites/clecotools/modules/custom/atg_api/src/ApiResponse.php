<?php

namespace Drupal\atg_api;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse extends JsonResponse {
  public function __construct($data = NULL, int $status = 200, array $headers = [], bool $json = FALSE) {
    $response = [
      'data' => $data
    ];
    parent::__construct($response, $status, $headers, $json);
  }
}
