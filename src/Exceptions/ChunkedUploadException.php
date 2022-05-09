<?php

namespace EgnytePhp\Egnyte\Exceptions;

use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;

class ChunkedUploadException extends \Exception {

  protected $position;

  /**
   * @return mixed
   */
  public function getPosition() {
    return $this->position;
  }

  /**
   * @param mixed $position
   */
  public function setPosition($position): void {
    $this->position = $position;
  }


}
