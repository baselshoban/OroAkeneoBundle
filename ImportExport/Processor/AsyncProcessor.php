<?php

namespace Oro\Bundle\AkeneoBundle\ImportExport\Processor;

use Oro\Bundle\ImportExportBundle\Processor\ProcessorInterface;

class AsyncProcessor implements ProcessorInterface
{
    use CacheProviderAwareProcessor;

    /** @var array */
    private $variants = [];

    public function process($item)
    {
        $this->updateVariants($item);

        return $item;
    }

    private function updateVariants(array &$item)
    {
        $identifier = $item['identifier'] ?? $item['code'];

        if (!empty($item['family_variant']) && empty($this->variants[$identifier])) {
            $this->variants[$identifier][$identifier] = ['parent' => $identifier, 'variant' => false];
        }

        if (empty($item['parent'])) {
            return;
        }

        $parent = $item['parent'];
        $this->variants[$parent][$identifier] = ['parent' => $parent, 'variant' => $identifier];
        unset($this->variants[$identifier][$identifier]);
    }

    public function initialize()
    {
        $this->variants = [];
        $this->cacheProvider->delete('product_variants');
    }

    public function flush()
    {
        if (!$this->variants) {
            return;
        }

        $resolvedVariants = [];

        foreach ($this->variants as $parent => $variants) {
            foreach ($variants as $variantKey => $variant) {
                if (array_key_exists($variantKey, $this->variants)) {
                    foreach ($this->variants[$variantKey] as $resolvedVariantKey => $resolvedVariant) {
                        $resolvedVariant['parent'] = $parent;
                        $resolvedVariants[$parent][$resolvedVariantKey] = $resolvedVariant;
                    }

                    continue;
                }

                $resolvedVariants[$parent][$variantKey] = $variant;
            }
        }

        $this->cacheProvider->save('product_variants', $resolvedVariants);
        $this->variants = [];
    }
}
