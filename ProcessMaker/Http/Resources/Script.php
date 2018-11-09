<?php

namespace ProcessMaker\Http\Resources;

// use Illuminate\Http\Resources\Json\ResourceCollection;

class Script extends ApiResource
{
    public function toArray($request)
    {
        $latest = $this->latestVersion();
        return array_merge(
            (new ApiResource($latest))->resolve(),
            parent::toArray($request),
            ['version_id' => $latest->id]
        );
    }
}
