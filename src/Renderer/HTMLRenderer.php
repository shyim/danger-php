<?php
declare(strict_types=1);

namespace Danger\Renderer;

use function count;
use Danger\Context;
use function str_replace;

class HTMLRenderer
{
    public const MARKER = '<!--- Danger-PHP-Marker -->';

    private const TABLE_TPL = <<<'TABLE'
        <table>
          <thead>
            <tr>
              <th></th>
              <th>##NAME##</th>
            </tr>
          </thead>
          <tbody>
            ##CONTENT##
          </tbody>
        </table>
        TABLE;

    private const ITEM_TPL = <<<'ITEM'
        <tr>
              <td>##EMOJI##</td>
              <td>##MSG##</td>
            </tr>
        ITEM;

    public function convert(Context $context): string
    {
        $content = self::MARKER;

        return
            $content .
            $this->render('Fails', ':no_entry_sign:', $context->getFailures()) .
            $this->render('Warnings', ':warning:', $context->getWarnings()) .
            $this->render('Notice', ':book:', $context->getNotices());
    }

    /**
     * @param string[] $entries
     */
    private function render(string $name, string $emoji, array $entries): string
    {
        if (count($entries) === 0) {
            return '';
        }

        $items = '';

        foreach ($entries as $entry) {
            $items .= str_replace(['##EMOJI##', '##MSG##'], [$emoji, $entry], self::ITEM_TPL);
        }

        return str_replace(['##NAME##', '##CONTENT##'], [$name, $items], self::TABLE_TPL);
    }
}
