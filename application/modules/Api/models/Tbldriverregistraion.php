<?php


class Api_Model_Tbldriverregistraion extends Zend_Db_Table_Abstract
{
   
    protected $_name = 'driver_registration';
    protected $_primary = 'id';
    protected $_db;
    
    protected $_sequence = 'true';
    
    public function __construct() 
    {
        $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter();
    }
    
    public function insertRow($data , $id= false)
    {
        try
        {
            $this->_db->insert($this->_name,$data);
            if($id == true)
            {
               $id = $this->_db->lastInsertId(); 
               return $id;
            }
            else
            {
                return true;
            }
        }
        catch(Exception $e)
        {
            return $e->getMessage();
        }
    }
    
    public function getLatestId()
    {  
        try
        {
            $select = $this->select()->from($this,  array(new Zend_Db_Expr('max(id) as LastInsertId')));
            $id=$this->fetchAll($select);
             if(null !== $id)
              {
                    $idArray=$id->toArray();
                    return $id=$idArray[0]['LastInsertId'];
              }
              else 
               {
                return $id;
                }
        }
        catch(Exception $e)
        {
            return $e->getMessage();
        }
    }   
    
    public function updateRow($data, $whereData = array())
    {	   
	
        foreach($whereData as $key=>$value)
        {
            $where[''.$key.''.' = ?']=$value;
        }
       
        try 
        {
		$this->update($data, $where);
		return true;			
        } 
	catch (Exception $e)
        {
		return $e->getMessage();
        }  
        
    }
    
    public function selectByMasterCondition($cols = null ,$where , $order = null , $limit = null)
    {
     
        try
        {
            $select = $this->select();
            if($cols !== null)
            {
                $select->from($this,$cols);
            }
            else
            {
                $select->from($this);
            }
            
            foreach($where as $operator=>$conditions)
            {
                foreach($conditions as $column=>$value)
                {
                    if(strtoupper($operator) == 'LIKE')
                    {
                         $select->where(''.$column.' LIKE (?)',"%{$value}%");
                         break;
                    }
                    else if(strtoupper($operator) == 'IN')
                    {
                        $select->where(''.$column.' IN (?)',$value);
                        break;
                    }
                    
                    $select->where(''.$column.' '.$operator.' ?',$value);
                }
            }
            if($order !== null)
            {
                $select->order($order);
            }
            if($limit !== null)
            {
                $select->limit($limit['count']);
            }
            
            $row = $this->fetchAll($select);
            if($row !== null)
            {
                return $row->toArray();
            }
            else
            {
                return $row;
            }
        }catch(Exception $e)
        {
            return $e->getMessage();
        }
    }  
    
    public function selectByColumn($data,$where,$limit = null)
    {    
        try
        {
         $select = $this->select()
        
            ->from($this,$data);//echo $select;die();
            foreach($where as $key=>$value)
            {
                $select->where(''.$key.' = ?',"$value");
            }
            if($limit !==null)
            {
                $select->limit($limit['count'],$limit['offset']);
            }

           $row =  $this->fetchAll($select);
            if(null !== $row)
            {
                return $row->toArray();
            }
            else 
            {
            return $row;  
            }
       }
       catch(Exception $e)
       {
           return $e->getMessage();
       }
    }
    
    public function selectByWhereInLike($data,$where= null,$inArray = null,$likeArray = null )
    {
       try
       {
         $select = $this->select()
        
            ->from($this,$data);//echo $select;die();
            if(null !== $where)
            {
                 foreach($where as $key=>$value)
                 {
                      $select->where(''.$key.' = ?',"$value");
                 }
           }

           if(null !== $inArray)
           {
                foreach($inArray as $inCol =>$inValue)
                {
                    $select->where(''.$inCol.' IN (?)',$inValue);
                }
          }

          if(null !== $likeArray)
           {
                foreach($likeArray as $likeCol =>$likeValue)
                {
                    $select->where(''.$likeCol.' LIKE (?)',"%{$likeValue}%");
                }
          }
          
            $row =  $this->fetchAll($select);
               if(null !== $row)
               {
                   return $row->toArray();
               }
               else 
               {
               return $row;  
               }
       }
       catch(Exception $e)
       {
           return $e->getMessage();
       }
                       
    }
    
    public function selectAllDataByCondition($whereData)
    {		
       try{

            foreach($whereData as $key=>$value)
            {
                $where[''.$key.''.' = ?']=$value;
            }

            $result = $this->fetchRow($where);
            if(null!==$result)
            {
                return $result->toArray();
            }else
            {   
                return $result;
            }
       }
       catch(Exception $e)
       {
           return $e->getMessage();
       }
    }
    
    public function selectRowByLimit ($offset = 0, $count =1, $data )
    {	
       	try {
                $select_qry = $this->select()
                                    ->from($this, $data)
                                    ->limit($count, $offset);//limit $offset $count
                $stmt = $select_qry->query();
                $result = $stmt->fetchAll();
                return $result;
            }
            catch (Exception $e){
                    return $e->getMessage();
            }
    }   
        
    public function selectByid($id)
    {
        try{
                return $this->find($id)->current();
           }
        catch (Exception $e){
                return $e->getMessage();
           }
    }
	
	
    public function selectByName($cols,$where)
    {
       try{           
           $select = $this->select()
                          ->from($this,$cols)
                         ->where($where);
         //echo $select;die();
           $row = $this->fetchRow($select);
          
           if(null !== null)
           {
               return $row->toArray();
           }
           else
           {
               return $row;
           }
       }
       catch(Exception $e)
       {
           return $e->getMessage();
       }
    } 
   
   	     
   public function selectByMaxUser($maxUser,$cols)
   {
       try
       {
           $select = $this->select()
                          ->from($this,$cols)
                          ->where('max_user_level = ?', $maxUser);
           $row = $this->fetchRow($select);
           if(null !== null)
           {
               return $row->toArray();
           }
           else
           {
               return $row;
           }
       }
       catch(Exception $e)
       {
           return $e->getMessage();
       }
   }
   
   
    public function selectByActive($cols, $active)
    {
       try
       {
           $select = $this->select()
                          ->from($this,$cols)
                          ->where('active = ?', $active);
           $row = $this->fetchAll($select);
           if(null !== $row)
           {
               return $row->toArray();
           }
           else
           {
               return $row;
           }
       }
       catch(Exception $e)
       {
           return $e->getMessage();
       }
    }
    
    public function selectData($cols, $where = null, $orderby = null, $offset = null, $count = null ,$having = null) {
        //$whereD = $this->_db->quoteInto($where, ' ');
        $select = $this->select()
                ->from($this, $cols);

        if ($where != null) {
            $select->where($where);
        }
        if ($having != null) {
            $select->having($having);
        }
        
        if ($orderby != null) {
            $select->order($orderby);
        }
        $select->limit($count, $offset);
//        echo $select;exit;
        $row = $this->fetchAll($select);
        if (!empty($row)) {
            return $row->toArray();
        } else {
            return false;
        }
    }
    

    
    
     public function selectData3($cols, $where = null, $groupby = null,$orderby = null, $offset = null, $count = null) {
  
        $select = $this->select()
                ->from($this, $cols);
        if ($where != null) {
            $select->where($where);
        }
        if ($groupby != null) {
            $select->group($groupby);
        }
        if ($orderby != null) {
            $select->order($orderby);
        }
        
        $select->limit($count, $offset);
//      print $select;exit;
        $row = $this->fetchAll($select);
        if (!empty($row)) {
            return $row->toArray();
        } else {
            return false;
        }
    }
   
   
}