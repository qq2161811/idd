<?php

declare(strict_types = 1);

namespace IDD\Framework\Contracts;

/**
 * 参数约定
 * Interface Parameter
 *
 * @package IDD\Framework\Contracts
 * @author  ShuQingZai<overbeck.jack@qq.com> 2022/1/14 2:34 PM
 */
interface Parameter
{
	/**
	 * 默认分页数
	 */
	public const PAGE = 1;
	/**
	 * 默认每页数量
	 */
	public const PAGE_SIZE = 20;
	/**
	 * 默认每页最大数量
	 */
	public const PAGE_MAX_SIZE = 100;
	/**
	 * 分页字段名
	 */
	public const PAGE_KEY = 'page';
	/**
	 * 每页数量字段名
	 */
	public const PAGE_SIZE_KEY = 'page_size';

	/**
	 * 默认排序字段名
	 */
	public const DEF_ORDER_BY_KEY = 'id';
	/**
	 * 排序字段名
	 */
	public const ORDER_BY_KEY = 'order_by';
	/**
	 * 升降序字段名
	 */
	public const SORT_BY_KEY = 'sort_by';
	/**
	 * 排序: asc=升序,desc=降序
	 */
	public const SORT_BY_DESC = 'desc';
	/**
	 * 排序: asc=升序,desc=降序
	 */
	public const SORT_BY_ASC = 'asc';
	/**
	 * 排序: asc=升序,desc=降序
	 */
	public const SORT_BY_LIST = [
		self::SORT_BY_ASC,
		self::SORT_BY_DESC,
	];
}