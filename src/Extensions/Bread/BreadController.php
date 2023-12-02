<?php

namespace VulcanPhp\PhpAdmin\Extensions\Bread;

use VulcanPhp\PhpAdmin\Extensions\DTS\DTS;
use VulcanPhp\PhpAdmin\Extensions\QForm\QForm;
use VulcanPhp\PhpRouter\Routing\Interfaces\IResource;
use VulcanPhp\PhpRouter\Routing\IRoute;
use VulcanPhp\Core\Foundation\Controller;

class BreadController extends Controller implements IResource
{
    public const LAYOUT_CONFIG = [
        'dir'   => __DIR__ . '/../../resources/views',
        'name'  => 'layout'
    ];

    public function __construct(protected IRoute $route)
    {
    }

    protected function config(): BreadConfig
    {
        return $this->route->bread;
    }

    public function index()
    {
        if ($this->config()->hasOverride('index')) {
            return $this->config()->applyOverride('index');
        }

        if ($this->config()->getViewMap('index') !== null) {
            return view($this->config()->getViewMap('index'));
        }

        DTS::Enqueue();

        $config = ['columns' => $this->config()->getConfig('columns'), 'actions' => $this->config()->getConfig('actions', [])['index'] ?? []];

        if ($this->config()->hasFilter('index')) {
            $config = $this->config()->applyFilter('index', $config);
        }

        return $this->breadView('index', $config);
    }

    public function show($id)
    {
        if ($this->config()->hasOverride('show')) {
            return $this->config()->applyOverride('show', $id);
        }

        if ($this->config()->getViewMap('show') !== null) {
            return view($this->config()->getViewMap('show'), ['id' => $id]);
        }

        $model = $this->config()->getModel()->find(['p.id' => $id]);

        if ($this->config()->hasFilter('show')) {
            $model = $this->config()->applyFilter('show', $model);
        }

        return $this->breadView('show', ['model' => $model]);
    }

    public function store()
    {
        if ($this->config()->hasOverride('store')) {
            return $this->config()->applyOverride('store');
        }

        if ($this->config()->getModel()->input() && $this->config()->getModel()->validate()) {
            if ($this->config()->getModel()->save()) {
                session()->setFlash('success', 'New record has been stored successfully.');
            } else {
                session()->setFlash('warning', 'Failed to store a new record.');
            }
        } else {
            session()->setFlash('warning', 'Failed on validate form.');
            return $this->create();
        }

        return response()->redirect(url(request()->route()->action() . '.create')->absoluteUrl());
    }

    public function create()
    {
        if ($this->config()->hasOverride('create')) {
            return $this->config()->applyOverride('create');
        }

        $form = QForm::begin($this->config()->getModel());

        foreach ((array)$this->config()->getConfig('form_fields') as $field) {
            $form->{$field['field']}($field);
        }

        $form->formAttr(['method' => 'post', 'action' => url(request()->route()->action() . '.store')->absoluteUrl()])
            ->submit(['name' => 'Create New', 'center' => true]);

        if ($this->config()->getViewMap('create') !== null) {
            return view($this->config()->getViewMap('create'), ['form' => $form]);
        }

        if ($this->config()->hasFilter('create')) {
            $form = $this->config()->applyFilter('create', $form);
        }

        return $this->breadView('create', ['form' => $form]);
    }

    public function edit($id)
    {
        if ($this->config()->hasOverride('edit')) {
            return $this->config()->applyOverride('edit', $id);
        }

        if ($this->config()->getViewMap('edit') !== null) {
            return view($this->config()->getViewMap('edit', ['id' => $id]));
        }

        $model = $this->config()->getModel()->find(['p.id' => $id]);

        if (!empty($this->config()->getConfig('actions', [])['edit']['mount'] ?? null)) {
            call_user_func($this->config()->getConfig('actions', [])['edit']['mount'], $model);
        }

        $form = QForm::begin($model);

        foreach ((array)$this->config()->getConfig('form_fields') as $field) {
            $form->{$field['field']}($field);
        }

        $form->formAttr(['method' => 'put', 'action' => url(request()->route()->action() . '.update', ['id' => $id])->absoluteUrl()])
            ->submit(['name' => 'Update', 'center' => true]);

        if ($this->config()->hasFilter('edit')) {
            $form = $this->config()->applyFilter('edit', $form);
        }

        return $this->breadView('edit', ['form' => $form]);
    }

    public function update($id)
    {
        if ($this->config()->hasOverride('update')) {
            return $this->config()->applyOverride('update', $id);
        }

        $model = $this->config()->getModel()
            ->load(['id' => $id])
            ->input();

        if ($this->config()->hasFilter('update')) {
            $model = $this->config()->applyFilter('update', $model);
        }

        if ($model->validate() && $model->save()) {
            session()->setFlash('success', 'Record has been saved successfully.');
        } else {
            if (!empty($model->firstError())) {
                session()->setFlash('warning', 'Failed, <b>' . $model->errorField() . '</b>: ' . $model->firstError());
            } else {
                session()->setFlash('warning', 'Failed to save record.');
            }
        }

        return response()->redirect(url(request()->route()->action() . '.edit', ['id' => $id])->absoluteUrl());
    }

    public function destroy($id)
    {
        if ($this->config()->hasOverride('destroy')) {
            return $this->config()->applyOverride('destroy', $id);
        }

        if ($this->config()->hasFilter('destroy')) {
            $id = $this->config()->applyFilter('destroy', $id);
        }

        if ($this->config()->getModel()->erase(['id' => $id])) {
            return response()->json(['message' => translate('Record has been deleted')]);
        }

        return response()->httpCode(500)->json(['message' => 'Failed! to delete record, please try again later.']);
    }

    public function data()
    {
        if ($this->config()->hasOverride('data')) {
            return $this->config()->applyOverride('data');
        }

        $ssp = DTS::model($this->config()->getModel()::class);
        $formatter = (array)$this->config()->getConfig('formatter');

        foreach ($this->config()->getConfig('columns') as $column) {

            $is_pause = stripos($column, 'pause:');
            $column   = str_ireplace(['pause:', 'orderDESC:', 'orderASC:'], '', $column);

            if (isset($formatter[$column]) && is_array($formatter[$column])) {
                $ssp->column(...$formatter[$column]);
            } else {
                $ssp->column($column, $formatter[$column] ?? null);
            }

            if ($is_pause !== false) {
                $ssp->pause();
            }
        }

        if (!empty($this->config()->getJoins())) {
            foreach ($this->config()->getJoins() as $action => $joins) {
                if (is_array($joins[0])) {
                    foreach ($joins as $join) {
                        $ssp->addJoin($action, ...$join);
                    }
                } else {
                    $ssp->addJoin($action, ...$joins);
                }
            }
        }

        $ssp->column(($ssp->hasJoins() ? 'p.' : '') . 'id', fn ($id) => $ssp->module('action', ['id' => $id, 'options' => array_merge($this->config()->getConfig('action_before', []), ['show', 'edit', 'destroy' => true]), 'route' => request()->route()->action()]));

        if (!empty($this->config()->getCondition())) {
            $ssp->where($this->config()->getCondition());
        }

        if (!empty($this->config()->getConfig('order'))) {
            $ssp->setOrder($this->config()->getConfig('order'));
        }

        return $ssp->render();
    }

    protected function breadView(string $template, array $args = [])
    {
        return view()
            ->getDriver()
            ->getEngine()
            ->resourceDir(__DIR__ . '/views')
            ->template($template)
            ->render(array_merge($args, [
                'title'  => $this->config()->getConfig('sidebar')['title'],
                'config' => $this->config()->getConfig('editor_config'),
                'layout' => self::LAYOUT_CONFIG
            ]));
    }
}
