# Custom Editable Emails for Silverstripe

Super easy to define them. 

```
---
name: notifications-config
---
SilverStripers\CustomEmails\Dev\Injector:
  definitions:
    EMAIL_IDENTIFIER:
      name: 'Readable name of the email'
      arguments: # merge tags
        - Name
        - Email
```

Sending emails

```
$processor = SilverStripers\CustomEmails\Model\NotificationEmail::get_processor(
    'EMAIL_IDENTIFIER',
    'to@email.com',
    'from@email.com',
    [
        'Name' => 'Name',
        'Email' => 'test@test.com'
    ]
);
$processor->setAttachments([
    'file_name' => $content
]);
$processor->send();
```

