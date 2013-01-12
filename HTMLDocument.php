<?php
require_once("HTMLElement.php");

class HTMLDocument
{
 	private $_document;

	public function __construct($htmlType = "XHTML transitional")
	{
		#TODO: doctype dependent of $htmlType
		$doctype = DOMImplementation::createDocumentType("html", "-//W3C//DTD XHTML 1.0 Transitional//EN", "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd");
		$this->_document = DOMImplementation::createDocument(null, 'html', $doctype);
		$html = $this->_document->documentElement;
		$html->setAttribute("xml:lang", "en");
		$html->setAttribute("lang", "en");
		{
			$head = $this->_document->createElement("head");
			$html->appendChild($head);

			$body = $this->_document->createElement("body");
			$html->appendChild($body);
		}
	}

	public function GetHead()
	{
		return new HTMLElement($this->_document->documentElement->getElementsByTagName("head")->item(0));
	}

	public function GetBody()
	{
		return new HTMLElement($this->_document->documentElement->getElementsByTagName("body")->item(0));
    }

    public function FindElementById($id)
    {
        $xpath = new DOMXPath($this->_document);
        $result = $xpath->Evaluate("//*[@id='$id']");
        if ($result->length == 1)
            return new HTMLElement($result->item(0));
        throw "no unique result: " . $result->length . "tags found";
    }

    public function FindElementByTag($tagName)
    {
        $xpath = new DOMXPath($this->_document);
        $result = $xpath->Evaluate("//$tagName");
        return new HTMLElement($result->item(0));
    }

    public function FindElementByXPath($path)
    {
        $xpath = new DOMXPath($this->_document);
        $result = $xpath->Evaluate($path);
        return $result->item(0);
    }

	public function Serialize($pretty)
	{
		$this->_document->formatOutput = $pretty;
    $xml = $this->_document->saveXML();
		return substr($xml, 22);
	}
}
?>
