<?php

namespace Haridarshan\Laravel\ApiVersioning;

use Nwidart\Modules\Json as NwidartJson;

class Json extends NwidartJson
{
    /**
     * @param array|null $data
     * @return false|string
     */
    public function toJsonPretty(array $data = null): bool|string
    {
        return json_encode($data ?: $this->attributes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}