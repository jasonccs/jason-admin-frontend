<?php

namespace Larke\Admin\Frontend\Model;

use Illuminate\Support\Facades\File;

/*
 * 菜单
 *
 * @create 2020-11-4
 * @author deatil
 */
class Menu
{
    /**
     * 检测格式
     *
     * @param boolen
     */
    public function validateInfo(array $info)
    {
        $mustInfo = [
            'id',
            'pid',
            'sort',
        ];
        if (empty($info)) {
            return false;
        }
        
        return !collect($mustInfo)
            ->contains(function ($key) use ($info) {
                return !isset($info[$key]);
            });
    }
    
    /**
     * 获取数据
     *
     * @param boolen
     */
    public function getFileData($file = null)
    {
        if (empty($file)) {
            $file = config('frontend.menu.file');
        }
        
        if (!File::exists($file)) {
            return '';
        }
        
        $data = File::get($file);
        
        return $data;
    }
    
    /**
     * 保存数据
     *
     * @param boolen
     */
    public function saveFileData($data, $file = null)
    {
        if (empty($data)) {
            return false;
        }
        
        if (empty($file)) {
            $file = config('frontend.menu.file');
        }
        
        $dirname = File::dirname($file);
        if (!File::exists($dirname)) {
            File::makeDirectory($dirname, 0755, true);
        }
        
        $status = File::put($file, $data, true);
        
        return $status;
    }
    
    public function read($file = null)
    {
        $data = $this->getFileData($file);
        if (empty($data)) {
            return [];
        }
        
        $content = json_decode($data, true);
        return $content;
    }
    
    public function save($content, $file = null)
    {
        $data = json_encode($content, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        
        $data = str_replace(': null', ': ""', $data);
        
        return $this->saveFileData($data, $file);
    }
    
    public function find($id)
    {
        if (empty($id)) {
            return [];
        }
        
        $menus = $this->read();
        if (empty($menus)) {
            return [];
        }
        
        foreach ($menus as $menu) {
            if ($menu['id'] == $id) {
                return $menu;
            }
        }
        
        return [];
    }
    
    public function delete($id)
    {
        if (empty($id)) {
            return false;
        }
        
        $menus = $this->read();
        if (empty($menus)) {
            return false;
        }
        
        foreach ($menus as $key => $menu) {
            if ($menu['id'] == $id) {
                unset($menus[$key]);
                return $this->save($menus);
            }
        }
        
        return false;
    }
    
    public function insert($data = [])
    {
        if (empty($data)) {
            return false;
        }
        
        $menus = $this->read();
        if (empty($menus)) {
            $menus = [];
        }
        
        $data['id'] = md5(mt_rand(100000, 999999).microtime());
        if (!isset($data['pid'])) {
            $data['pid'] = 0;
        }
        
        if (!isset($data['sort'])) {
            $data['sort'] = 100;
        }
        
        $data['sort'] = intval($data['sort']);
        
        ksort($data);
        
        $menus[] = $data;
        
        return $this->save($menus);
    }
    
    public function update($id, $data = [])
    {
        if (empty($id)) {
            return false;
        }
        
        $validateStatus = $this->validateInfo($data);
        
        if (!$validateStatus) {
            return false;
        }
        
        $menus = $this->read();
        if (empty($menus)) {
            return false;
        }
        
        foreach ($menus as $key => $menu) {
            if ($menu['id'] == $id) {
                $menus[$key] = $data;
                return true;
            }
        }
        
        return false;
    }
    
    public function getList()
    {
        $menus = $this->read();
        if (empty($menus)) {
            return [];
        }
        
        return $menus;
    }
    
    public function getTree($childType = 'child')
    {
        if (empty($childType)) {
            return [];
        }
        
        $menus = $this->getList();
        if (empty($menus)) {
            return [];
        }
        
        // 根据 sort 键值正序排序
        $menus = collect($menus)->sortBy('sort')->toArray();
        
        $menusTree = $this->list2tree($menus, 'id', 'pid', $childType);
        
        return $menusTree;
    }
    
    /**
     * 获取第一层级子菜单
     * @param string $id 菜单id
     * @return array
     */
    public function getChildren($id)
    {
        if (empty($id)) {
            return false;
        }
        
        $menus = $this->read();
        if (empty($menus)) {
            return false;
        }
        
        $list = collect($menus)->map(function($menu) use($id) {
            if (isset($menu['pid']) && $menu['pid'] == $id) {
                return $menu;
            }
            
            return [];
        })->filter(function($data) {
            return !empty($data);
        });
        
        return $list;
    }
    
    /**
     * 获取权限菜单列表
     */
    public function getAuthList()
    {
        $menus = $this->getList();
        if (empty($menus)) {
            return [];
        }
        
        $roles = app('larke.admin')->getRuleids();
        
        $list = collect($menus)->filter(function($data) use($roles) {
            if (in_array($data['id'], $roles)) {
                return true;
            }
            
            return false;
        })->values();
        
        return $list;
    }
    
    /**
     * 获取权限菜单树
     */
    public function getAuthTree($childType = 'child')
    {
        if (empty($childType)) {
            return [];
        }
        
        $menus = $this->getAuthList();
        if (empty($menus)) {
            return [];
        }
        
        // 根据 sort 键值正序排序
        $menus = collect($menus)->sortBy('sort')->toArray();
        
        $menusTree = $this->list2tree($menus, 'id', 'pid', $childType);
        
        return $menusTree;
        
    }
    
    /**
     * 把返回的数据集转换成Tree
     */
    public function list2tree(
        $list, 
        $pk = 'id', 
        $pid = 'pid', 
        $child = 'child', 
        $root = 0
    ) {
        // 创建Tree
        $tree = [];
        if (is_array($list)) {
            // 创建基于主键的数组引用
            $refer = [];
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] = &$list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId = $data[$pid];
                
                if ((string) $root == (string) $parentId) {
                    $tree[] = &$list[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent = &$refer[$parentId];
                        $parent[$child][] = &$list[$key];
                    }
                }
            }
        }
        
        return $tree;
    }
    
}
