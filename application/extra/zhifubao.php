<?php
$config = array (
    //应用ID,您的APPID。
    'app_id' => "2018090561265452",

    //商户私钥
    'merchant_private_key' => "MIIEpQIBAAKCAQEA+OSkUljgSE9Ct3Tf/KkD8+8yQrcTxiLNTLD5M9lwzPIMmvGZY6DjhrUM6E4qD+vVhpYuLnqPFdFWvT1hNgMyNoCmibxP7xvYj5r32TgAb8s6zV4pJt2+zKDcJWjx9TocKA54kuKMS2U8USwqUkgxOqkkRDcoM775SqkMquO+jqIu7Syz9mbhT+mTMqo4JKDVQ+IxxD6Xa+GNAfxWwSMIAKGRaxMEJljPg3Yih6lmgoPESZ47/PMaW9pOeYv6dcguN62Yfxm/NvrYrnrOQrxr1NIEnVrCbLEkyD5FqVYNafur7coEFLOjikEuYis9ld43UijqEidsh3GC9Dc5bfyaDQIDAQABAoIBAHpGsef22/EKZ4eDtssFKfj8iZ+3G9LapCvKQhPT/pPhUooIqcgFceJrLjUuuYFq5lMhkvyon4UjfE6qSgjkFxYprCe8yTR6ZLFge+VOmwBRQslEOklq5WLYoG8MmNpWOyD+wwO+oxgjWzexz/TunKjqEfQleO27I/QnCx8lliex0/3VRah41fo15GfY6JdLsoIGWbuCxO+5jwK0qQvlcpVgNvLQ4RIrZL9/tunaUrYpuDe8tgLk/GcJ7ivNW+xXC6eMWJ4Hs3RINk9NEWfvRR25Ly9bizVTKa1y1xbTHlIoZ1s3LLnB6QM8rxrmw9hPCeCJDcjXgKR0hIdWCzbklk0CgYEA/p1yONSx1z6aQf+MWeGjed8mcq7oK5DLr6mVt4sDfTXunK+bNtT+to2s05N7SG8FmPmG0xMHm+hgDRizRgRgnpe4pzDVkpqHkmE8Gg+iM89PWHizl6udqfnEUtUCiwsFiW+VmY2mojBacwFa3dTenwb4Tcz0mg1L2gW7uqHRZyMCgYEA+j86WIvv7+iFoDw1zirIzVebeZuwwfIjt6vPXPNtBXZGwWhz0ccmqa2URYkHp1zy/a+jyaIrbQ3SGtmB58kKkMn2UyPywnTvrAwZVhdIxZcNUtcyLb6uDFJjXQCguIPxqYNIAAICPcs87TPoTwhBtNdkLawI/uyBfJkBCWqqpQ8CgYEA4lroy9tTS28tRlVA2js90T/wd25Fm017t2xFXMoqTOtgeU2o6INM+tBTADmSFWAWEtxq30WAsztQAPSflDSaDQQHNiO1C0N0GU92Vhjl87du69FKoCEC5rTUs2sJesFOp9NapQuIQ5JHJwziUmpHjAtvPgNixX3inC4SqiXn+w8CgYEAgOy6Pow53TvKPDdI8SKRuVj9PLW9Zu49AI9/kb/H1xyMb3BE2zri7GAFF531V4BHn/MxStxFyzVnnXWZu7STwOyL/2Fx3EUqhVTmaLguQb4Emz1LGM44FRkNIAkGxIkVo+OC4J4oUXW3ue0YLj9uuqCNeAo9yDhIrcyWACf4KnkCgYEApAWYrA6T4nEhBstIo4eohGiycQsVAa0bbkmqwKmN5WPa1LLyU9+H1fBW0hpzSQOY+Z7ooiOFos/Wi68cobyayAtwqImKlyqpFdcy0P6BGvX+ua7A38vEbJGcKMJVaaabX8xHiZM2H9ySO4xQDbn3l/8zjLlfp9nxqA/h/K8o0Bs=",

    //异步通知地址
    'notify_url' => "https://www.shuddd.com/notify",

    //同步跳转
    'return_url' => "https://www.shuddd.com/success",

    //编码格式
    'charset' => "UTF-8",

    //签名方式
    'sign_type'=>"RSA2",

    //支付宝网关
    'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

    //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
    'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA+OSkUljgSE9Ct3Tf/KkD8+8yQrcTxiLNTLD5M9lwzPIMmvGZY6DjhrUM6E4qD+vVhpYuLnqPFdFWvT1hNgMyNoCmibxP7xvYj5r32TgAb8s6zV4pJt2+zKDcJWjx9TocKA54kuKMS2U8USwqUkgxOqkkRDcoM775SqkMquO+jqIu7Syz9mbhT+mTMqo4JKDVQ+IxxD6Xa+GNAfxWwSMIAKGRaxMEJljPg3Yih6lmgoPESZ47/PMaW9pOeYv6dcguN62Yfxm/NvrYrnrOQrxr1NIEnVrCbLEkyD5FqVYNafur7coEFLOjikEuYis9ld43UijqEidsh3GC9Dc5bfyaDQIDAQAB",
);
return $config;