<?php


/**
 * @file
 * @author
 * @date
 * @brief
 *
 */
// abstract DBResult
abstract class Fis_Db_AbsDBResult
{
    abstract public function next($type = Fis_Db::FETCH_ASSOC);

    abstract public function seek($where);

    abstract public function tell();

    abstract public function count();

    abstract public function free();

    public function walk($callback, $type = Fis_Db::FETCH_ASSOC)
    {
        // seek head
        if ($this->tell() != 0 && !$this->seek(0))
        {
            return false;
        }

        $args = func_get_args();
        if (count($args) <= 2)
        {
            $args = array();
        }
        else
        {
            array_shift($args);
            array_shift($args);
        }

        $count = 0;
        while ($row = $this->next($type))
        {
            $count++;
            $tmp = $args;
            array_unshift($tmp, $row);
            if (call_user_func_array($callback, $tmp) === false)
            {
                break;
            }
        }
        return $count;
    }
}
/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
