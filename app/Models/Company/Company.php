<?php

namespace App\Models\Company;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

class Company extends Eloquent
{
    use Filterable, SoftDeletes, Sortable;

    protected $table = 'companies';

    protected $dates = ['deleted_at'];

    protected $fillable = ['domain'];

    /**
     * Sortable columns.
     *
     * @var array
     */
    public $sortable = ['domain'];

    public function accounts()
    {
        return $this->hasMany('App\Models\Banking\Account');
    }

    public function bill_histories()
    {
        return $this->hasMany('App\Models\Expense\BillHistory');
    }

    public function bill_items()
    {
        return $this->hasMany('App\Models\Expense\BillItem');
    }

    public function bill_payments()
    {
        return $this->hasMany('App\Models\Expense\BillPayment');
    }

    public function bill_statuses()
    {
        return $this->hasMany('App\Models\Expense\BillStatus');
    }

    public function bills()
    {
        return $this->hasMany('App\Models\Expense\Bill');
    }

    public function categories()
    {
        return $this->hasMany('App\Models\Setting\Category');
    }

    public function currencies()
    {
        return $this->hasMany('App\Models\Setting\Currency');
    }

    public function customers()
    {
        return $this->hasMany('App\Models\Income\Customer');
    }

    public function invoice_histories()
    {
        return $this->hasMany('App\Models\Income\InvoiceHistory');
    }

    public function invoice_items()
    {
        return $this->hasMany('App\Models\Income\InvoiceItem');
    }

    public function invoice_payments()
    {
        return $this->hasMany('App\Models\Income\InvoicePayment');
    }

    public function invoice_statuses()
    {
        return $this->hasMany('App\Models\Income\InvoiceStatus');
    }

    public function invoices()
    {
        return $this->hasMany('App\Models\Income\Invoice');
    }

    public function items()
    {
        return $this->hasMany('App\Models\Item\Item');
    }

    public function payments()
    {
        return $this->hasMany('App\Models\Expense\Payment');
    }

    public function revenues()
    {
        return $this->hasMany('App\Models\Income\Revenue');
    }

    public function settings()
    {
        return $this->hasMany('App\Models\Setting\Setting');
    }

    public function taxes()
    {
        return $this->hasMany('App\Models\Setting\Tax');
    }

    public function transfers()
    {
        return $this->hasMany('App\Models\Banking\Transfer');
    }

    public function users()
    {
        return $this->morphedByMany('App\Models\Auth\User', 'user', 'user_companies', 'company_id', 'user_id');
    }

    public function vendors()
    {
        return $this->hasMany('App\Models\Expense\Vendor');
    }

    public function setSettings()
    {
        $settings = $this->settings;

        foreach ($settings as $setting) {
            list($group, $key) = explode('.', $setting->getAttribute('key'));

            // Load only general settings
            if ($group != 'general') {
                continue;
            }

            $value = $setting->getAttribute('value');

            if (($key == 'company_logo') && empty($value)) {
                $value = 'public/img/company.png';
            }

            $this->setAttribute($key, $value);
        }

        // Set default default company logo if empty
        if ($this->getAttribute('company_logo') == '') {
            $this->setAttribute('company_logo', 'public/img/company.png');
        }
    }

    /**
     * Define the filter provider globally.
     *
     * @return ModelFilter
     */
    public function modelFilter()
    {
        list($folder, $file) = explode('/', \Route::current()->uri());

        if (empty($folder) || empty($file)) {
            return $this->provideFilter();
        }

        $class = '\App\Filters\\' . ucfirst($folder) .'\\' . ucfirst($file);

        return $this->provideFilter($class);
    }

    /**
     * Scope to get all rows filtered, sorted and paginated.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $sort
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCollect($query, $sort = 'name')
    {
        $request = request();

        $input = $request->input();
        $limit = $request->get('limit', setting('general.list_limit', '25'));

        return $this->filter($input)->sortable($sort)->paginate($limit);
    }
}