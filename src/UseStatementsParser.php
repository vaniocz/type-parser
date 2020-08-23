<?php
namespace Vanio\TypeParser;

use Doctrine\Common\Annotations\TokenParser;

/**
 * @final
 */
class UseStatementsParser
{
    /**
     * @param \ReflectionClass|string $class
     * @return string[]
     */
    public function parseClass($class): array
    {
        if (!$class instanceof \ReflectionClass) {
            $class = new \ReflectionClass($class);
        }

        if (!$file = $class->getFilename()) {
            return [];
        }

        return $this->parseContent($this->getFileContent($file, $class->getStartLine()), $class->getNamespaceName());
    }

    /**
     * @param string $namespace
     * @param string $file
     * @param bool $merge Whether to merge use statements of multiple namespace declarations with the given name
     * @return string[]
     */
    public function parseNamespace(string $namespace, string $file, bool $merge = true): array
    {
        return $this->parseContent($this->getFileContent($file), $namespace, $merge);
    }

    /**
     * @param string $content
     * @param string $namespace
     * @param bool $merge Whether to merge use statements of multiple namespace declarations with the given name
     * @return string[]
     */
    public function parseContent(string $content, string $namespace, bool $merge = false): array
    {
        if (!$content) {
            return [];
        }

        return $this->parseUseStatements($content, $namespace, $merge);
    }

    private function getFileContent(string $fileName, int $numberOfLines = null): string
    {
        try {
            $file = new \SplFileObject($fileName);
        } catch (\Throwable $e) {
            return '';
        }

        $line = 0;
        $content = '';

        while (!$file->eof()) {
            if ($line++ === $numberOfLines) {
                break;
            }

            $content .= $file->fgets();
        }

        return $content;
    }

    /**
     * @param string $content
     * @param string $namespace
     * @param bool $merge
     * @return string[]
     */
    private function parseUseStatements(string $content, string $namespace, bool $merge = false): array
    {
        $tokenParser = new TokenParser('<?php ' . $this->removeContentBeforeNamespace($content, $namespace));
        $useStatements = [];
        $currentNamespace = '';

        while ($token = $tokenParser->next()) {
            if ($currentNamespace === $namespace && $token[0] === T_USE) {
                foreach ($tokenParser->parseUseStatement() as $alias => $useStatement) {
                    $useStatements[$alias] = ltrim($useStatement, '\\');
                }
            } elseif ($token[0] === T_NAMESPACE) {
                $currentNamespace = $tokenParser->parseNamespace();

                if ($useStatements && !$merge && $currentNamespace === $namespace) {
                    $useStatements = [];
                }
            } elseif ($token[0] === T_CLASS) {
                $this->skipClassDeclaration($tokenParser, $token);
            }
        }

        return $useStatements;
    }

    private function removeContentBeforeNamespace(string $content, string $namespace): string
    {
        if (!$namespace) {
            return $content;
        }

        $pattern = sprintf('~^.*?(\bnamespace\s+%s\s*[;{].*)$~s', preg_quote($namespace));
        $content = preg_replace($pattern, '\\1', $content, -1, $count);

        return $count ? $content : '';
    }

    /**
     * @param TokenParser $tokenParser
     * @param mixed[] $token
     */
    private function skipClassDeclaration(TokenParser $tokenParser, array $token)
    {
        $braceLevel = 0;
        $firstBraceFound = false;

        while ($braceLevel > 0 || !$firstBraceFound) {
            if (
                $token[0] === '{'
                || $token[0] === T_CURLY_OPEN
                || $token[0] === T_DOLLAR_OPEN_CURLY_BRACES
            ) {
                $firstBraceFound = true;
                $braceLevel++;
            } elseif ($token[0] === '}') {
                $braceLevel--;
            }

            if (!$token = $tokenParser->next()) {
                return;
            }
        }
    }
}
