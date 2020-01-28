<?php

namespace FSPoster\App\Providers;

/**
 * Class DB
 * @package FSPoster\App\Providers
 */
class DB
{

	const PLUGIN_DB_PREFIX = 'fs_';

	/**
	 * @return \wpdb
	 */
	public static function DB()
	{
		global $wpdb;

		return $wpdb;
	}

	/**
	 * @param $tbName
	 *
	 * @return string
	 */
	public static function table( $tbName )
	{
		return self::DB()->base_prefix . self::PLUGIN_DB_PREFIX . $tbName;
	}

	/**
	 * @param $table
	 * @param null $where
	 *
	 * @return mixed
	 */
	public static function fetch( $table , $where = null )
	{
		$whereQuery = '';
		$argss = [];
		$where = is_numeric($where) && $where > 0 ? [$where] : $where;
		if( !empty($where) && is_array($where) )
		{
			$whereQuery =  '';

			foreach($where AS $filed => $value)
			{
				$filed = $filed === 0 ? 'id' : $filed;
				$whereQuery .= ($whereQuery == '' ? '' : ' AND ') . $filed.'=%s';
				$argss[] = (string)$value;
			}

			$whereQuery =  ' WHERE ' . $whereQuery;
		}

		if( empty($argss) )
		{
			return DB::DB()->get_row("SELECT * FROM " . DB::table($table) . $whereQuery ,ARRAY_A );
		}

		return DB::DB()->get_row(
			DB::DB()->prepare("SELECT * FROM " . DB::table($table) . $whereQuery , $argss)
			,ARRAY_A
		);

	}

	/**
	 * @param $table
	 * @param null $where
	 *
	 * @return mixed
	 */
	public static function fetchAll( $table , $where = null )
	{
		$whereQuery = '';
		$argss = [];
		$where = is_numeric($where) && $where > 0 ? [$where] : $where;
		if( !empty($where) && is_array($where) )
		{
			$whereQuery =  '';

			foreach($where AS $filed => $value)
			{
				$filed = $filed === 0 ? 'id' : $filed;
				$whereQuery .= ($whereQuery == '' ? '' : ' AND ') . $filed.'=%s';
				$argss[] = (string)$value;
			}

			$whereQuery =  ' WHERE ' . $whereQuery;
		}

		if( empty($argss) )
		{
			return DB::DB()->get_results("SELECT * FROM " . DB::table($table) . $whereQuery ,ARRAY_A );
		}

		return DB::DB()->get_results(
			DB::DB()->prepare("SELECT * FROM " . DB::table($table) . $whereQuery , $argss)
			,ARRAY_A
		);

	}


}