<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposedCompany extends Model
{
    protected $table = 'proposed_companies';

    protected $primaryKey = 'proposed_company_id';

    // Không có timestamps
    public $timestamps = false;

    protected $fillable = [
        'name',
        'address',
        'website',
        'tax_code',
        'contact_email',
    ];

    // ===================== RELATIONSHIPS =====================

    public function internshipRequests()
    {
        return $this->hasMany(InternshipRequest::class, 'proposed_company_id', 'proposed_company_id');
    }
}
