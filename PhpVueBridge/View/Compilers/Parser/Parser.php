<?php

namespace PhpVueBridge\View\Compilers\Parser;

use PhpVueBridge\View\Compilers\Parser\Tokens\EchoToken;
use PhpVueBridge\View\Compilers\Parser\Tokens\TextToken;
use PhpVueBridge\View\Compilers\Exceptions\ParserException;
use PhpVueBridge\View\Compilers\Parser\Tokens\HtmlTagToken;
use PhpVueBridge\View\Compilers\Parser\Tokens\CustomTagToken;
use PhpVueBridge\View\Compilers\Parser\Tokens\DirectiveToken;
use PhpVueBridge\View\Compilers\Parser\Tokens\ExpressionToken;

class Parser
{

    /**
     * The state in which the current position is parsing
     */
    protected int $state = 0;

    /**
     * This indicate that is going to be parsed text
     */
    const TEXT_STATE = 0;

    /**
     * This indicate that is going to be parsed a html tag
     */
    const TAG_STATE = 1;

    /**
     * This indicate that is going to be parsed a directive tag
     */
    const DIRECTIVE_STATE = 2;

    /**
     * This indicate that is going to be parsed a echo content tag
     */
    const ECHO_STATE = 3;

    /**
     * All the tokens found the current content
     */
    protected array $tokens = [];

    /**
     * Where is the current position cursor is located in all the content
     */
    protected int $current;

    /**
     * The total length of the content
     */
    protected int $length;

    /**
     * The # of line in the content
     */
    protected int $lineno = 0;

    /**
     * All the positions found in the content
     */
    protected array $positions = [];

    /**
     * All the custom tags found in the content
     */
    protected array $customTags = [];

    /**
     * All the html 5 tags that are no present in html 4
     * This is needed becouse the DOMDocument Api of php works with html 4 
     * And to proper identify the custom tags 
     */
    protected array $html5Tags = array(
        'article',
        'aside',
        'audio',
        'canvas',
        'datalist',
        'details',
        'embed',
        'figcaption',
        'figure',
        'footer',
        'header',
        'hgroup',
        'keygen',
        'mark',
        'meter',
        'nav',
        'output',
        'progress',
        'section',
        'source',
        'time',
        'video'
    );

    public function __construct(
        protected string $content
    ) {
        $this->length = strlen($content);

        $positions = [];

        // To find all the html tags in the content we are going to use the DOMDocument API from php
        // Set the internal error handle to true so we can catch any errors
        libxml_use_internal_errors(true);

        // Create a new DOMDocument object 
        $dom = new \DOMDocument();
        // And load the content as a html document
        if (!$dom->loadHTML('<?xml encoding="UTF-8">' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_COMPACT)) {
            // If failed to load the content throw an exception
            throw new ParserException('Failed to load the content');
        }

        // Then we iterate through all the errors found in the DOM Api 
        // To search for errors with code 801 and the messsage "Tag %s Invalid"
        // These errors means that is a custom tag in other words a possible Component
        foreach (libxml_get_errors() as $error) {
            if (
                $error->code == 801
                && preg_match('/Tag(.*?)invalid/', $error->message, $matches)
            ) {
                $tagName = trim($matches[1]);
                if (!in_array($tagName, $this->html5Tags)) {
                    $this->customTags[] = $tagName;
                }
            }
        }

        // Clean all the errors and set the internal error back to false
        libxml_clear_errors();
        libxml_use_internal_errors(false);

        // Get all the tags of the document
        $tags = $dom->getElementsByTagName('*');

        // The offset array of the tags founded in the content
        $offsetArray = [];

        // Iterate over the tags returned by the DOM Api
        foreach ($tags as $tag) {

            // Escape the tag name to put currectly in the regex pattern
            $tagName = preg_quote($tag->nodeName);

            // Find the positions of the tags within the content
            if (preg_match_all(
                "/<{$tagName}[^>]*>|<\/{$tagName}>|<{$tagName}[^>]*\/>/i",
                $content,
                $matches,
                PREG_OFFSET_CAPTURE | PREG_SET_ORDER,
            )) {
                // Iterate all over the founded tags in the content
                // If is a tag not already founded then added to the array of positions and offset array
                foreach ($matches as $match) {
                    if (!in_array($match[0][1], $offsetArray)) {
                        $offsetArray[] = $match[0][1];
                        $positions[] = [[$match[0][0], $match[0][1], $tag->nodeName]];
                    }
                }
            }
        }

        // Find all positions that begin with the content tags
        preg_match_all('/\B@(@?\w+(?:::\w+)?)/x', $this->content, $matches, \PREG_OFFSET_CAPTURE | \PREG_SET_ORDER);

        $positions = array_merge($positions, $matches);

        // Find all positions that has a HTML tag
        preg_match_all('/\{\{|\}\}/', $this->content, $matches, \PREG_OFFSET_CAPTURE | \PREG_SET_ORDER);

        $positions = array_merge($positions, $matches);

        $this->prependPositions($positions);
    }

    protected function prependPositions(array $positions)
    {
        $positions = array_merge(
            $positions,
            $this->positions
        );

        // Order all the positions in order of position
        usort($positions, fn ($first, $second) => $first[0][1] > $second[0][1] ? 1 : -1);

        $this->positions = $positions;
    }

    public function parse()
    {
        // Set the current position to 0 and set the text state
        $this->current = 0;
        $this->state = self::TEXT_STATE;

        while ($this->current < $this->length) {

            if ($this->state == self::TEXT_STATE) {
                $this->parseTextState();
            }

            if ($this->state == self::DIRECTIVE_STATE) {
                $this->parseDirectiveState();
            }

            if ($this->state == self::TAG_STATE) {
                $this->parseHtmlTag();
            }

            if ($this->state == self::ECHO_STATE) {
                $this->parseEchoState();
            }
        }

        $this->addToken(
            Token::EOF_TOKEN,
            ['']
        );

        return new TokenStream($this);
    }

    protected function parseTextState(): void
    {
        // Verify that are left positions to parse
        if (count($this->positions) <= 0) {
            $this->addToken(Token::TEXT_TOKEN, [substr($this->content, $this->current, $this->length - $this->current)]);
            $this->current = $this->length;
            return;
        }

        // Find the first position
        $position = reset($this->positions);

        // Get all the text before the first position
        $textContent = substr($this->content, $this->current, $position[0][1] - $this->current);

        // If there is text before the first position then add a new token of type text
        if (strlen($trimmedContent = trim($textContent)) > 0) {
            // Add the text content before the current position as a token of type text
            $this->addToken(Token::TEXT_TOKEN, [$trimmedContent]);
        }

        // Move the current position to the end of the text content
        $this->moveCurrent($textContent);

        // Verify what is the current position state
        // if it starts with a @ then it means that that is a directive
        if (str_starts_with($position[0][0], '@')) {
            $this->setState(self::DIRECTIVE_STATE);
        }
        // if it starts with a < then it means that it is a html tag
        else if (str_starts_with($position[0][0], '<')) {
            $this->setState(self::TAG_STATE);
        }
        // if not, then it means that that is a echo content
        else {
            $this->setState(self::ECHO_STATE);
        }
    }

    protected function parseHtmlTag(): void
    {

        $position = array_shift($this->positions);
        [$content, $pos, $tagName] = $position[0];

        // if it is a close html tag
        $isClose = false;

        // if it is a auto close html tag
        $isAutoClose = false;

        // if it starts with a < then it means that it is a open html tag
        if (str_starts_with($content, '</')) {
            $isClose = true;
        }
        // if it ends with a /> then it means that it is a auto close html
        else if (str_ends_with($content, '/>')) {
            $isAutoClose = true;
        }

        $length = strlen($content);

        while ($next = reset($this->positions)) {

            if ($next[0][1] > $pos + $length) {
                break;
            }

            // if the next position is lower than the current position length
            // and it starts with @ then it means that is a directive inside a html tag
            if (str_starts_with($next[0][0], '@')) {
                $this->parseInnerDirective($length);
            }
            // else if it is a echo open content tag 
            else if ($next[0][0] === '{{') {
                $this->parseInnerEcho();
            }
            // if not there is something that we dont want to parse
            else {
                array_shift($this->positions);
            }
        }

        if ($isAutoClose) {
            $this->addToken(
                (in_array($tagName, $this->customTags)) ? Token::AUTO_CLOSE_CUSTOM_TAG_TOKEN : Token::AUTO_CLOSE_TAG_TOKEN,
                [$content, $tagName]
            );
        } else if ($isClose) {
            $this->addToken(Token::CLOSE_TAG_TOKEN, [$content, $tagName]);
        } else {
            $this->addToken(
                (in_array($tagName, $this->customTags)) ? Token::OPEN_CUSTOM_TAG_TOKEN : Token::OPEN_TAG_TOKEN,
                [$content, $tagName]
            );
        }

        // Move the current position and set the text state
        $this->moveCurrent($content);
        $this->setState(self::TEXT_STATE);
    }

    protected function parseInnerDirective(int $max = null): void
    {
        $this->parseDirectiveState(true, $max);
    }

    protected function parseDirectiveState(bool $isInner = false, int $max = null): void
    {
        $position = array_shift($this->positions);
        $content = '';

        // Get the length of the directive
        $length = strlen($position[0][0]);

        // When it should start searchin for the directive arguments
        $start = $position[0][1] + $length;

        // Find the directive arguments and if there is arguments added to the token
        if (is_array($argumentsPositions = $this->findDirectiveArguments($start, $max))) {

            [$open, $close] = $argumentsPositions;

            $this->addToken(
                ($isInner) ? Token::IN_TAG_DIRECTIVE_TOKEN : Token::DIRECTIVE_TOKEN,
                [
                    $position[1][0],
                    // Parse the directive arguments and add them to the token
                    $this->parseDirectiveArguments(
                        $arguments = substr($this->content, $start, $close - $open - ($start - $open)),
                    )
                ]
            );

            $content = $position[0][0] . $arguments;
        }
        // If not add a simple directive whitout arguments
        else {
            $this->addToken(
                ($isInner) ? Token::IN_TAG_DIRECTIVE_TOKEN : Token::DIRECTIVE_TOKEN,
                [$position[1][0]]
            );
            $content = $position[0][0];
        }

        $totalLength = strlen($content);

        // Delete all the position inside the directive arguments
        while ($next = reset($this->positions)) {
            if ($next[0][1] > $position[0][1] + $totalLength) {
                break;
            }

            array_shift($this->positions);
        }

        //Move the current and set the state back to text to find the next position
        // if not a inner directive
        if (!$isInner) {
            $this->moveCurrent($content);
            $this->setState(self::TEXT_STATE);
        }
    }

    protected function findDirectiveArguments(int $start, int $max = null): array|bool
    {
        // Set that there is not foud the parentesis
        $openParentesis = -1;
        $closeParentesis = -1;

        // The total of parenthesis found
        $totalOpen = 0;
        $totalClose = 0;

        // The offset
        $i = 0;

        // While there is not found the close parenthesis or it reaches the end of the file
        while ($closeParentesis === -1 && $start + $i < $this->length) {

            // if it is present a max parameter
            // limit the iteration to the max parameter
            if (!is_null($max)) {
                if ($i >= $max) {
                    return false;
                }
            }

            // Get the char at the start position + the offset
            $char = $this->content[$start + $i];

            // If the char is a end of line
            // and the is not found the open parenthesis then we say that the directive not have arguments
            if ($char === PHP_EOL || $char === "\n" || $char === "\r") {
                if ($openParentesis === -1) {
                    return false;
                }
            }

            // If we found a open parenthesis
            // if is the first open parenthesis then we set that parenthesis as the start position of the arguments
            // and incriese the total of opening parameters
            if ($char === '(') {
                if ($openParentesis === -1) {
                    $openParentesis = $start + $i;
                }

                $totalOpen++;
            }

            // If we found a close parenthesis then we are going incrementing the total of closing parenthesis
            // and the total of opening and closing parenthesis is equal then we say that we found the closing parenthesis
            // and set the close position of the arguments
            if ($char === ')') {

                $totalClose++;

                if ($totalClose == $totalOpen) {
                    $closeParentesis = $start + $i + 1;
                    break;
                }
            }

            // If we found a diferent character that a space and is not found the open parenthesis we say that the directive not have arguments
            if ($char !== ' ' && $openParentesis === -1) {
                return false;
            }

            // increment the offset
            $i++;
        }

        // if is not found the open or close parenthesis we say that the directive not have arguments
        if ($openParentesis === -1 || $closeParentesis === -1) {
            return false;
        }

        return [$openParentesis, $closeParentesis];
    }

    protected function parseDirectiveArguments(string $arguments)
    {
        return trim($arguments);
    }

    protected function parseInnerEcho(): void
    {
        $this->parseEchoState(true);
    }

    protected function parseEchoState(bool $isInner = false)
    {

        // The start position of the echo state
        $startPosition = array_shift($this->positions);

        // Add the open echo token
        $this->addToken(
            ($isInner) ? Token::IN_TAG_OPEN_CONTENT_TOKEN : Token::OPEN_CONTENT_TOKEN,
            [$startPosition[0][0]]
        );

        // The end position of the echo state it should be the next position
        // Iterate through the nexts position until we find the close echo token
        while ($endPosition = array_shift($this->positions)) {
            if ($endPosition[0][0] === '}}') {
                break;
            }
        }

        $echoContent = substr($this->content, $startPosition[0][1] + 2, $endPosition[0][1] - $startPosition[0][1] - 2);

        $this->addToken(
            Token::EXPRESION_TOKEN,
            [$echoContent]
        );

        // add the close echo token
        $this->addToken(
            Token::CLOSE_CONTENT_TOKEN,
            [$endPosition[0][0]]
        );


        // Move the current and set the state back to text to find the next position
        // If not is a inner echo content
        if (!$isInner) {
            $this->moveCurrent($startPosition[0][0] . $echoContent . $endPosition[0][0]);
            $this->setState(self::TEXT_STATE);
        }
    }

    protected function addToken(string $token, array $data): void
    {
        if (in_array($token, array(Token::OPEN_TAG_TOKEN, Token::CLOSE_TAG_TOKEN, Token::AUTO_CLOSE_TAG_TOKEN))) {
            $this->tokens[] = new HtmlTagToken($token, ...$data);
        } else if (in_array($token, array(Token::OPEN_CUSTOM_TAG_TOKEN, Token::AUTO_CLOSE_CUSTOM_TAG_TOKEN))) {
            $this->tokens[] = new CustomTagToken($token, ...$data);
        } else if (in_array($token, array(Token::DIRECTIVE_TOKEN, Token::IN_TAG_DIRECTIVE_TOKEN))) {
            $this->tokens[] = new DirectiveToken($token, ...$data);
        } else if (in_array($token, array(
            Token::CLOSE_CONTENT_TOKEN, Token::OPEN_CONTENT_TOKEN, Token::IN_TAG_OPEN_CONTENT_TOKEN
        ))) {
            $this->tokens[] = new EchoToken($token, ...$data);
        } else if ($token === Token::EXPRESION_TOKEN) {
            $this->tokens[] = new ExpressionToken($token, ...$data);
        } else {
            $this->tokens[] = new TextToken($token, ...$data);
        }
    }

    protected function moveCurrent(string $parsed): void
    {
        $this->current += strlen($parsed);
        $this->lineno = substr_count($this->content, PHP_EOL);
    }

    protected function setState(int $state): void
    {
        $this->state = $state;
    }

    public function getTokens(): array
    {
        return $this->tokens;
    }
}
