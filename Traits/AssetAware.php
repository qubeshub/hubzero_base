<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2015 HUBzero Foundation, LLC.
 *
 * This file is part of: The HUBzero(R) Platform for Scientific Collaboration
 *
 * The HUBzero(R) Platform for Scientific Collaboration (HUBzero) is free
 * software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * HUBzero is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2015 HUBzero Foundation, LLC.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

namespace Hubzero\Base\Traits;

use Hubzero\Document\Asset\Image;
use Hubzero\Document\Asset\Javascript;
use Hubzero\Document\Asset\Stylesheet;
use Hubzero\Component\ControllerInterface;
use Hubzero\Plugin\Plugin;
use Hubzero\Module\Module;

/**
 * Asset Aware trait.
 * Adds helpers for pushing CSS and JS assets to the document.
 */
trait AssetAware
{
	/**
	 * Push CSS to the document
	 *
	 * @param   string  $stylesheet  Stylesheet or styles to add
	 * @param   string  $extension   Extension name, e.g.: com_example, mod_example, plg_example_test
	 * @param   string  $element     Plugin element. Only used for plugins and if first argument is folder name.
	 * @return  object
	 */
	public function css($stylesheet = '', $extension = null, $element = null)
	{
		$extension = $extension ?: $this->detectExtensionName();

		$attr = array(
			'type'    => 'text/css',
			'media'   => null,
			'attribs' => array()
		);

		if ($element)
		{
			if (is_string($element))
			{
				$extension = 'plg_' . $extension . '_' . $element;
			}
			else if (is_array($element))
			{
				foreach ($element as $key => $val)
				{
					if (array_key_exists($key, $attr))
					{
						$attr[$key] = $val;
					}
				}
			}
		}

		$asset = new Stylesheet($extension, $stylesheet);

		if ($asset->exists())
		{
			if ($asset->isDeclaration())
			{
				\App::get('document')->addStyleDeclaration($asset->contents());
			}
			else
			{
				\App::get('document')->addStyleSheet($asset->link(), $attr['type'], $attr['media'], $attr['attribs']);
			}
		}
		return $this;
	}

	/**
	 * Push JS to the document
	 *
	 * @param   string  $asset      Script to add
	 * @param   string  $extension  Extension name, e.g.: com_example, mod_example, plg_example_test
	 * @param   string  $element    Plugin element. Only used for plugins and if first argument is folder name.
	 * @return  object
	 */
	public function js($asset = '', $extension = null, $element = null)
	{
		$extension = $extension ?: $this->detectExtensionName();

		$attr = array(
			'type'  => 'text/javascript',
			'defer' => false,
			'async' => false
		);

		if ($element)
		{
			if (is_string($element))
			{
				$extension = 'plg_' . $extension . '_' . $element;
			}
			else if (is_array($element))
			{
				foreach ($element as $key => $val)
				{
					if (array_key_exists($key, $attr))
					{
						$attr[$key] = $val;
					}
				}
			}
		}

		$asset = new Javascript($extension, $asset);

		if ($asset->exists())
		{
			if ($asset->isDeclaration())
			{
				\App::get('document')->addScriptDeclaration($asset->contents());
			}
			else
			{
				\App::get('document')->addScript($asset->link(), $attr['type'], $attr['defer'], $attr['async']);
			}
		}
		return $this;
	}

	/**
	 * Get the path to an image
	 *
	 * @param   string  $asset      Image name
	 * @param   string  $extension  Extension name, e.g.: com_example, mod_example, plg_example_test
	 * @param   string  $element    Plugin element. Only used for plugins and if first argument is folder name.
	 * @return  string
	 */
	public function img($asset, $extension = null, $element = null)
	{
		$extension = $extension ?: $this->detectExtensionName();

		if ($element)
		{
			$extension = 'plg_' . $extension . '_' . $element;
		}

		$asset = new Image($extension, $asset);

		return $asset->link();
	}

	/**
	 * Determine the extension the view is being called from
	 *
	 * @return  string
	 */
	private function detectExtensionName()
	{
		if ($this instanceof Plugin)
		{
			return 'plg_' . $this->_type . '_' . $this->_name;
		}
		else if ($this instanceof ControllerInterface)
		{
			return \Request::getCmd('option', $this->_option);
		}
		else if ($this instanceof Module)
		{
			return $this->module->module;
		}

		return '';
	}
}
