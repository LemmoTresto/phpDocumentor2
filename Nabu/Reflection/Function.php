<?php
class Nabu_Reflection_Function extends Nabu_Reflection_BracesAbstract
{
  protected $name       = '';
  protected $doc_block  = null;
  protected $arguments_token_start = 0;
  protected $arguments_token_end   = 0;

  protected $arguments     = array();

  protected function processGenericInformation(Nabu_TokenIterator $tokens)
  {
    $this->name = $this->findName($tokens);

    $this->resetTimer();
    $this->doc_block  = $this->findDocBlock($tokens);

    list($start_index, $end_index) = $tokens->getTokenIdsOfParenthesisPair();
    $this->arguments_token_start = $start_index;
    $this->arguments_token_end   = $end_index;
    $this->debugTimer('    determined argument range token ids');
  }

  public function processVariable(Nabu_TokenIterator $tokens)
  {
    // is the variable occurs within arguments parenthesis then it is an argument
    if (($tokens->key() > $this->arguments_token_start) && ($tokens->key() < $this->arguments_token_end))
    {
      $this->resetTimer('variable');

      $argument = new Nabu_Reflection_Argument();
      $argument->parseTokenizer($tokens);
      $this->arguments[$argument->getName()] = $argument;

      $this->debugTimer('    Processed argument '.$argument->getName(), 'variable');
    }
  }

  protected function findName(Nabu_TokenIterator $tokens)
  {
    return $tokens->findNextByType(T_STRING, 5, array('{'))->getContent();
  }

  public function getName()
  {
    return $this->name;
  }

  public function getDocBlock()
  {
    return $this->doc_block;
  }

  public function __toString()
  {
    return $this->getName();
  }

  public function __toXml()
  {
    $xml = new SimpleXMLElement('<function></function>');
    $xml->name = $this->getName();
    $this->addDocblockToSimpleXmlElement($xml);

    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->loadXML($xml->asXML());

    foreach ($this->arguments as $argument)
    {
      $this->mergeXmlToDomDocument($dom, $argument->__toXml());
    }

    return trim($dom->saveXML());
  }
}