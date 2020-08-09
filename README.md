## Requisitos
- PHP >= 7.2.5
- BCMath PHP Extension
- Ctype PHP Extension
- Fileinfo PHP extension
- JSON PHP Extension
- Mbstring PHP Extension
- OpenSSL PHP Extension
- PDO PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension

## Instalação

- Clone o repositório em alguma pasta da sua máquina
- Rode o comando ``` composer install ```
- Configure o arquivo ``` .env ``` com as credenciais do seu banco de dados e o valor ``` QUEUE_CONNECTION=database ```
- Rode as migrations com o comando ``` php artisan migrate ```
- Logo após rodar as migrations, rodar o comando ``` php artisan db:seed ``` para realizar os testes com usuário local

## Endpoints

POST /api/v1/transaction

```
{
    "value" : 100.00,
    "payer" : 2,
    "payee" : 4
}
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
