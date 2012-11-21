<?php
require_once("errorhandling.php");

class HTMLElement
{
	private $_element = null;
	public function __construct($domElement)
	{
		$this->_element = $domElement;
	}

	public function AddTag($name, $attributes = array(), $text = null)
	{
		$tag = $this->_element->ownerDocument->createElement($name);
		foreach ($attributes as $key => $value)
		{
			$tag->setAttribute($key, $value);
		}
		if (isset($text))
		{
			$tag->nodeValue = $text;
		}
		$this->_element->appendChild($tag);

		return new HTMLElement($tag);
	}

	public function AddComment($comment)
	{
		$this->_element->appendChild($this->_element->ownerDocument->createComment($comment));

		return $this;
	}

	public function SetAttribute($attr, $value)
	{
		$this->_element->setAttribute($attr, $value);

		return $this;
	}

	public function AppendChild($domElement)
	{
		$this->_element->appendChild($this->_element->ownerDocument->importNode($domElement, true));

		return $this;
	}

	public function AddHTMLFragment($fragment)
	{
		$toReplace = array();
		$toReplace["/&nbsp;/"] = "&#xA0;";
		$toReplace["/&agrave;/"] = "&#xE0;";
		$toReplace["/&egrave;/"] = "&#xE8;";
		$toReplace["/&eacute;/"] = "&#xE9;";
		$toReplace["/&euml;/"] = "&#xEB;";
		$toReplace["/&iuml;/"] = "&#xEF;";
		$toReplace["/&ouml;/"] = "&#xF6;";
		$toReplace["/&hellip;/"] = "&#x2026;";
		$toReplace["/&euro;/"] = "&#x20AC;";
		$fragment = preg_replace(array_keys($toReplace), array_values($toReplace), "<span>$fragment</span>");
		$this->AppendChild(DOMDocument::loadXML($fragment)->documentElement);
		return $this;
	}
}
?>
