<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'transaction_id' => $this->id,
            'asset_code' => $this->asset_id,
            'ops_id' => $this->ops_id,
            'status' => $this->status,
            'check_out' => optional($this->check_out)->format('Y-m-d H:i:s'),
            'check_in' => optional($this->check_in)->format('Y-m-d H:i:s'),
            'created_by' => $this->user->email,
            'remarks' => $this->remarks,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
