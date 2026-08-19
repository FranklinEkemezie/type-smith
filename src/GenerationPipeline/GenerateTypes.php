<?php

declare(strict_types=1);

namespace TypeSmith\GenerationPipeline;

use Throwable;
use TypeSmith\Exceptions\UnsupportedLanguageException;
use TypeSmith\Formatting\Formatter;
use TypeSmith\Output\FileWriters\FileWriter;
use TypeSmith\SourceResolution\SourceResolverChain;
use TypeSmith\TypeGeneration\TypeGeneratorRegistry;
use TypeSmith\TypeParsing\TypeParserChain;

class GenerateTypes
{
    public function __construct(
        protected SourceResolverChain $sourceResolver,
        protected TypeParserChain $typeParser,
        protected TypeGeneratorRegistry $typeGeneratorRegistry,
        protected Formatter $typeFormatter,
        protected FileWriter $fileWriter,
    ) {}

    public function execute(GenerationRequest $request): GenerationResult
    {
        $result = new GenerationResult;

        try {

            $generator = $this->typeGeneratorRegistry->get($request->language);
            if ($generator === null) {
                throw new UnsupportedLanguageException($request->language);
            }

            $classes = $this->sourceResolver->resolve($request->source);
            if (empty($classes)) {
                return $result;
            }

            foreach ($classes as $class) {
                /** @var class-string<*> $class */
                try {

                    $stage = GenerationStage::Parsing;
                    $typeDefinition = $this->typeParser->parse($class);

                    $stage = $stage->next();
                    $generatedType = $generator->generate($typeDefinition);

                    $result->addGeneratedType($generatedType);

                    $stage = $stage->next();
                    if ($request->shouldFormat) {
                        $generatedType = $this->typeFormatter->format($generatedType);
                        $result->addFormattedType($generatedType);
                    }

                    $stage = $stage->next();
                    $generatedFile = $this->fileWriter->write($generatedType, $request->output);

                    $result->addGeneratedFile($generatedFile);

                } catch (Throwable $e) {
                    $result->addError($stage, $e, $class);
                }
            }
        } catch (Throwable $e) {
            $result->addError(GenerationStage::Resolving, $e);
        }

        return $result;
    }
}
