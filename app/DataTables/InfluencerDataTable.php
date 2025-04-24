<?php
namespace App\DataTables;

use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\EloquentDataTable;

class InfluencerDataTable 
{
    public function dataTable($query)
    {
        $dataTable = new EloquentDataTable($query);
        return $dataTable;
        // return $dataTable->addColumn('action', 'admin::admin_types.datatables_actions');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AdminType $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AdminType $model)
    {
        return DB::select('user_id', 'social_media_link', 'followers', 'user_reach');
                
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            // ->addAction(['width' => '120px', 'printable' => false])
            ->parameters([
                'dom'       => 'Bfrtip',
                'ordering'=>false,
                'stateSave' => false,
                'order'     => [[0, 'desc']],
                'buttons'   => [
                  
                ],
            ]);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            'user_id',
            'social_media_link',
            'followers',
            'user_reach',
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'admin_types_datatable_' . time();
    }
}
