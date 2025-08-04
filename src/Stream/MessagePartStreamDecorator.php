<?php
/**
 * This file is part of the ZBateson\MailMimeParser project.
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace ZBateson\MailMimeParser\Stream;

use GuzzleHttp\Psr7\StreamDecoratorTrait;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use ZBateson\MailMimeParser\Message\IMessagePart;

/**
 * Provides a readable stream for a MessagePart.
 *
 * @author Zaahid Bateson
 */
class MessagePartStreamDecorator implements StreamInterface
{
    use StreamDecoratorTrait {
        read as private decoratorRead;
    }

    /**
     * @var IMessagePart The part to read from.
     */
    protected IMessagePart $part;

    protected ?StreamInterface $stream;

    public function __construct(IMessagePart $part, ?StreamInterface $stream = null)
    {
        $this->part = $part;
        $this->stream = $stream ?? Utils::streamFor('');
    }

    /**
     * Overridden to wrap exceptions in MessagePartReadException which provides
     * 'getPart' to inspect the part the error occurs on.
     *
     * @throws MessagePartStreamReadException
     */
    public function read($length) : string
    {
        try {
            return $this->decoratorRead($length);
        } catch (MessagePartStreamReadException $me) {
            throw $me;
        } catch (RuntimeException $e) {
            throw new MessagePartStreamReadException(
                $this->part,
                'Exception occurred reading a part stream: cid=' . $this->part->getContentId()
                . ' type=' . $this->part->getContentType() . ', message: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
    public function getSize(): ?int
    {
        return $this->stream->getSize();
    }

    public function getContents(): string
    {    
        return $this->stream->getContents();
    }

    public function seek($offset, $whence = self::SEEK_SET)
    {
        $this->stream->seek($offset, $whence);
    }
    public function rewind(): void
    {    
        $this->stream->rewind();
    }
    public function write($string)
    {    
        return $this->stream->write($string);
    }
    public function eof(): bool
    {    
        return $this->stream->eof();
    }
    public function tell(): int
    {    
        return $this->stream->tell();
    }
    public function isReadable(): bool
    {    
        return $this->stream->isReadable();
    }
    public function isWritable(): bool
    {    
        return $this->stream->isWritable();
    }
    public function isSeekable(): bool
    {    
        return $this->stream->isSeekable();
    }
    public function getMetadata($key = null)
    {    
        return $this->stream->getMetadata();
    }
}
