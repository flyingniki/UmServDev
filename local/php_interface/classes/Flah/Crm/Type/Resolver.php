<?php

namespace Flah\Crm\Type;

use \Bitrix\Main,
	\Bitrix\Crm,
	\Bitrix\Crm\Service\Container,
	\Bitrix\Main\DI\ServiceLocator
	;

Main\Loader::requireModule('crm');

class Resolver
{
	static $typeInMemoryCache;
 
	static $useCache = true;

	/**
	 * Return entity type id foy dynamic entity by type code
	 * @param  string $code Dynamic entity code
	 * @return string
	 */
	public static function resolveEntityTypeNameByCode( $code )
	{
		static::ensureLoadedTypes();

		foreach (static::$typeInMemoryCache as $type)
		{
			if ( $type->getCode() != $code )
			{
				continue;
			}

			return \CCrmOwnerType::ResolveName($type->getEntityTypeId());
		}

		return \CCrmOwnerType::ResolveName( \CCrmOwnerType::Undefined );
	}

	/**
	 * Return entity type id foy dynamic entity by type code
	 * @param  string $code Dynamic entity code
	 * @return integer
	 */
	public static function resolveEntityTypeIdByCode( $code )
	{
		static::ensureLoadedTypes();

		foreach (static::$typeInMemoryCache as $type)
		{
			if ( $type->getCode() != $code )
			{
				continue;
			}

			return $type->getEntityTypeId();
		}

		return \CCrmOwnerType::Undefined;
	}

	/**
	 * Return stage entity id by entity code
	 * @param  string $code 
	 * @param  array $options 
	 * @return string
	 */
	public static function resolveStageEntityIdByCode( $code, array $options = [] )
	{
		static::ensureLoadedTypes();

		foreach (static::$typeInMemoryCache as $type)
		{
			if ( $type->getCode() != $code )
			{
				continue;
			}

			if ( !$type->getIsStagesEnabled() )
			{
				throw new \Exception("Requested stage entity, but stages not acceptable");
			}
	
			$factory = Container::getInstance()
				->getFactory( $type->getEntityTypeId() );

			if ( array_key_exists('categoryId', $options) )
			{
				if ( !$type->getIsCategoriesEnabled() )
				{
					throw new \Exception("Requested stage entity with category, but categories not acceptable");
				}

				foreach ($factory->getCategories() as $category)
				{
					if ( $options['categoryId'] != $category->getId() )
					{
						continue;
					}

					return $factory->getStagesEntityId($category->getId());
				}

			}

			return $factory->getStagesEntityId();
		}

		return "";
	}

	/**
	 * Return category id by category name
	 * @param  string $code 
	 * @param  string $categoryName
	 * @return int
	 */
	public static function resolveCategoryIdByName( $code, $categoryName )
	{
		static::ensureLoadedTypes();

		foreach (static::$typeInMemoryCache as $type)
		{
			if ( $type->getCode() != $code )
			{
				continue;
			}

			if ( !$type->getIsCategoriesEnabled() )
			{
				throw new \Exception("Categories disabled");
			}
	
			$factory = Container::getInstance()
				->getFactory( $type->getEntityTypeId() );

			foreach ($factory->getCategories() as $category)
			{
				if ( $category->getName() !== $categoryName )
				{
					continue;
				}

				return $category->getId();
			}
		}

		return 0;
	}

	/**
	 * Return default category id
	 * @param  string $code 
	 * @return int
	 */
	public static function resolveDefaultCategoryId( $code )
	{
		static::ensureLoadedTypes();

		foreach (static::$typeInMemoryCache as $type)
		{
			if ( $type->getCode() != $code )
			{
				continue;
			}

			$factory = Container::getInstance()
				->getFactory( $type->getEntityTypeId() );

			$defaultCategory = $factory->getDefaultCategory();

			if ( !empty($defaultCategory) )
			{
				return $defaultCategory->getId();
			}
		}

		return 0;
	}

	/**
	 * Load inmemory cache
	 * @return boolean
	 */
	protected static function ensureLoadedTypes()
	{
		if ( static::$useCache && is_array(static::$typeInMemoryCache) )
		{
			return true;
		}

		static::$typeInMemoryCache = [];

		$typeCollection = Container::getInstance()->getDynamicTypesMap()
			->getTypesCollection();

		foreach ($typeCollection as $type)
		{
			static::$typeInMemoryCache[] = $type;
		}

		return true;
	}

	/**
	 * Enable use cache
	 * @return void
	 */
	public static function enableUseCache(): void
	{
		static::$useCache = true;
	}

	/**
	 * Disable use cache
	 * @return void
	 */
	public static function disableUseCache(): void
	{
		static::$useCache = false;
	}
}