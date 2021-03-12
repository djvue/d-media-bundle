<?php

namespace Djvue\DMediaBundle\Tests\App\EventListener;

use App\Controller\BaseApiController;
use App\Exceptions\ValidatorException;
use JetBrains\PhpStorm\ArrayShape;
use Psr\Log\LoggerInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public function __construct(
        private bool $debug,
        // private LoggerInterface $logger,
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        /* @see example in https://github.com/symfony/symfony/blob/5.x/src/Symfony/Component/Security/Http/Firewall/ExceptionListener.php */
        $exception = $event->getThrowable();
        try {
            do {
                if ($exception instanceof HttpException) {
                    $this->handleHttpException($event, $exception);

                    return;
                }
                $this->handleOtherExceptions($event, $exception);
            } while (null !== $exception = $exception->getPrevious());
        } catch (\Exception $e) {
            $f = FlattenException::createFromThrowable($e);

            $this->logException($e, sprintf('Exception thrown when handling an exception (%s: %s at %s line %s)', $f->getClass(), $f->getMessage(), $e->getFile(), $e->getLine()));

            $prev = $e;
            do {
                if ($exception === $wrapper = $prev) {
                    throw $e;
                }
            } while ($prev = $wrapper->getPrevious());

            $prev = new \ReflectionProperty($wrapper instanceof \Exception ? \Exception::class : \Error::class, 'previous');
            $prev->setAccessible(true);
            $prev->setValue($wrapper, $exception);

            throw $e;
        }
    }

    public function handleHttpException(ExceptionEvent $event, HttpException $exception): void
    {
        $event->allowCustomResponseCode();
        $data = [
            'success' => false,
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'data' => !$this->debug ? [] : $this->getExceptionDebugData($exception),
        ];
        $response = new JsonResponse($this->json($data), $exception->getStatusCode(), [], true);
        $event->setResponse($response);
        $e = FlattenException::createFromThrowable($event->getThrowable());
        $this->logException($exception, sprintf(
            'Uncaught HTTP Exception %s: "%s" at %s line %s',
            $e->getClass(),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        ));
    }

    public function handleOtherExceptions(ExceptionEvent $event, \Throwable $exception): void
    {
        $event->allowCustomResponseCode();
        if (!$this->debug) {
            $data = [
                'success' => false,
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'server error',
                'data' => [],
            ];
        } else {
            $data = [
                'success' => false,
                'code' => $exception->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $exception->getMessage(),
                'data' => $this->getExceptionDebugData($exception),
            ];
        }
        $response = new JsonResponse($this->json($data), Response::HTTP_INTERNAL_SERVER_ERROR, [], true);
        $event->setResponse($response);
        $e = FlattenException::createFromThrowable($event->getThrowable());
        $this->logException($exception, sprintf(
            'Uncaught PHP Exception %s: "%s" at %s line %s',
            $e->getClass(),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        ));
    }

    /**
     * Logs an exception.
     */
    protected function logException(\Throwable $exception, string $message): void
    {
        if (!$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500) {
            // $this->logger->critical($message, ['exception' => $exception]);
        } else {
            // $this->logger->error($message, ['exception' => $exception]);
        }
    }

    #[ArrayShape(['exception' => 'string', 'file' => 'string', 'line' => 'int', 'trace' => 'array'])]
    private function getExceptionDebugData(\Throwable $exception): array
    {
        return [
            'exception' => $exception::class,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTrace(),
        ];
    }

    private function json(array $data): string
    {
        if ($this->debug) {
            return $this->prettyJson($data);
        }

        return $this->minifiedJson($data);
    }

    private function minifiedJson(array $data): string
    {
        return json_encode($data);
    }

    private function prettyJson(array $data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
