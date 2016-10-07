<?php

namespace Quintype\Api;

use ArrayObject;

class Bulk
{
  public function __construct($client) {
    $this->requests = [];
    $this->base = new BaseFunctions($client);
  }

  public function addBulkRequest($name, $request, $params) {
    $this->requests[$name] = (new StoriesRequest($request))->addParams($params);
    return $this;
  }

  public function executeBulk($client) {
    $requests = [];
    foreach($this->requests as $key => $value) {
      $requests[$key] = $value->toBulkRequest();
    }
    $apiResponse = $this->getStories($requests);
    $responses = [];
    foreach($this->requests as $key => $value) {
      $responses[$key] = $value->fromBulkResponse($apiResponse[$key]);
    }
    $this->responses = $responses;
  }

  public function getBulkResponse($name) {
    return $this->responses[$name];
  }

  private function getStories($requestPayload){
    $query = '/api/v1/bulk';
    $response = $this->base->postRequest($query, ["requests" => $requestPayload]);//Get the stories.
    return $response['results'];
  }
}

class Story extends ArrayObject
{

}

class StoriesRequest
{
  public function __construct($storyGroup) {
    $this->params = ["story-group" => $storyGroup, "_type" => "stories"];
  }

  public function addParams($params) {
    $this->params = array_merge($this->params, $params);
    return $this;
  }

  public function toBulkRequest() {
    return $this->params;
  }

  public function fromBulkResponse($response) {
    return array_map(function ($s) {
      return new Story($s);
    }, $response["stories"]);
  }
}