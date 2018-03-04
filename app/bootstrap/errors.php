<?php

$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        return $container['view']
            ->render($response, 'errors/404.twig')
            ->withHeader('Content-type', 'text/html')
            ->withStatus(404);
    };
};

$container['notAllowedHandler'] = function ($container) {
    return function ($request, $response, $methods) use ($container) {
        return $container['view']
            ->render($response, 'errors/405.twig', array("methods" => $methods))
            ->withStatus(405)
            ->withHeader('Allow', implode(', ', $methods))
            ->withHeader('Content-type', 'text/html');
    };
};

$container['errorHandler'] = function ($container) {
    return function ($request, $response, $exception) use ($container) {

        if (filter_var($container['config']['error-email'], FILTER_VALIDATE_EMAIL)) {
            $email = new \Dappur\Dappurware\Email($container);
            $email->sendEmail(
                array(
                    $container['config']['error-email']),
                "PHP Error on " . $container['config']['site-name'],
                "<pre>" . $exception . "</pre>",
                $exception
            );
        }

        return $container['view']
            ->render($response, 'errors/500.twig', array("exception" => $exception))
            ->withStatus(500)
            ->withHeader('Content-type', 'text/html');
    };
};

$container['phpErrorHandler'] = function ($container) {
    return function ($request, $response, $exception) use ($container) {
        if (filter_var($container['config']['error-email'], FILTER_VALIDATE_EMAIL)) {
            $email = new \Dappur\Dappurware\Email($container);
            $email->sendEmail(
                array(
                    $container['config']['error-email']),
                "Application Error on " . $container['config']['site-name'],
                "<pre>" . $exception . "</pre>",
                $exception
            );
        }

        return $container['view']
            ->render($response, 'errors/500-php.twig', array("exception" => $exception))
            ->withStatus(500)
            ->withHeader('Content-type', 'text/html');
    };
};
