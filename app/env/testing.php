<?php

putenv("APPLICATION_ENV=testing");

putenv('RABBITMQ_URL=http://guest:guest@localhost:5672');
putenv('RABBITMQ_VHOST=/test-behat');
