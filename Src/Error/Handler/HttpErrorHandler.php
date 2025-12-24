<?php
declare(strict_types=1);

namespace Design\Error\Handler;

use Design\Error\ExceptionMapperInterface;
use Design\Error\Object\HttpError;
use Design\Error\Render\ErrorRendererInterface;
use Design\Logging\LoggerInterface;
use Throwable;

final readonly class HttpErrorHandler
{
    /**
     * @param ExceptionMapperInterface[] $mappers
     */
    public function __construct(
        private ErrorRendererInterface $renderer,
        private LoggerInterface $logger,
        private array $mappers,
    ) {}

    /**
     * @param callable():void $run
     */
    public function handle(callable $run): void
    {
        try {
            $run();
        } catch (Throwable $e) {
            $httpError = $this->map($e);

            $context = [
                'status' => $httpError->status,
                'type' => get_class($e),
                'message' => $e->getMessage(),
            ];
            if ($httpError->requestId !== null) {
                $context['requestId'] = $httpError->requestId;
            }

            if ($httpError->status >= 500) {
                $this->logger->error('HTTP error', $context);
            } else {
                $this->logger->warning('HTTP error', $context);
            }

            $this->renderer->render($httpError->status, $httpError->publicMessage, $httpError->requestId);
        }
    }

    private function map(Throwable $e): HttpError
    {
        foreach ($this->mappers as $mapper) {
            if ($mapper instanceof ExceptionMapperInterface && $mapper->supports($e)) {
                return $mapper->map($e);
            }
        }

        $requestId = bin2hex(random_bytes(6));
        return new HttpError(500, 'Something went wrong. Please try again.', $requestId);
    }
}
