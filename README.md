# Funcionamento

## Rotas

### Novo contato/negociação/empresa

```php
Route::post('/BotNewContact', [CadastraOportunidadeController::class, 'Index'])
// {{url}}/api/BotNewContact
```

acionado quando é ativada a trigger do chatbot
busca informações do contato do DigiSac através das informações do bot
cria organização (apenas com nome)

```json
// BODY DEFAULT
{
    "organization": {
        "name": "{contact_name}"
    }
}
```

cria contato e atrela a organização

```json
// BODY DEFAULT
{
    "contact": {
        "name": "{contact_name}",
        "organization_id": "{organization_id}",
        "phones": [
            {
                "phone": "{contact_number}"
            }
        ]
    }
}
```

cria negociação

```json
// BODY DEFAULT
{
    "deal_source": {
        "_id": "629f76e4f8bd9e0012cea59a" // CLIENTE ATIVO
    },
    "deal": {
        "deal_stage_id": "629f76e4f8bd9e0012cea59c", // EM CONTATO
        "name": "Negociação"
    },
    "distribution_settings": {
        "owner": {
            "type": "all"
        }
    },
    "organization": {
        "_id": "{organization_id}"
    },
    "contacts": {
        {
            "name": "{contact_name}",
            "phones": [
                {
                    "phone": "{contact_number}"
                }
            ]
        }
    }
}
```


### Atualiza empresa

```php
Route::get('/UpdateOrganization', [RDIntegrationController::class, 'UpdateOrganization'])
// {{url}}/api/UpdateOrganization?message=xxxxxxxxxxxxxx
```

acionado quando um vendedor seleciona a oção de atualizar o RD
busca o texto da mensagem de onde foi disparada a requisição
retorna uma página com os dados mapeados para confirmar se os dados estão corretos

```php
Route::post('/SendToRd', [SendToRd::class, 'Send'])
// {{url}}/api/SendToRd
```

acionado quando os dados são confirmados através do forms
busca no banco de dados a organização a qual o contato foi atrelado inicialmente
atualiza a organização no RD com os dados confirmados


```json
{
    "organization": {
        "organization_custom_fields": {
          {
            "value": "{cpf_cnpj}",
            "custom_field_id": "6495b874762aa6000f8ea83d" //CPF/CNPJ
          },
          {
            "value": "{razao_social}",
            "custom_field_id": "66216ece48208900114653b8" //Razão Social
          },
          {
            "value": "{ie}",
            "custom_field_id": "66216ee4dd3387001bc0edd3" //Inscrição Estadual
          },
          {
            "value": "{cep}",
            "custom_field_id": "66216f0aed7897000f9e113f" //CEP
          },
          {
            "value": "{endereco}",
            "custom_field_id": "66216f01d700ad000f579068" //Endereço
          },
          {
            "value": "{bairro}",
            "custom_field_id": "66216ec9ed7897000f9e10d3" //Bairro
          },
          {
            "value": "{estado}",
            "custom_field_id": "66216fcfb15e2e000ef1d45e" //Estado
          },
          {
            "value": "{email}",
            "custom_field_id": "66216ec6a2fa4e00153e944a" //Email
          }
        }
    }
}
```



## Utilização

Novo contato passando pelo bot
1. Cliente entra em contato e é atendido pelo bot
2. O bot automaticamente chama um webhook da API
3. A API verifica se o cliente já foi cadastrado no banco de dados anteriormente
    - Sim: não executa nenhum procedimento
    - Não: cadastra uma nova organização e um novo contato. Salva o nome, telefone e o id da organização no banco de dados

Cliente fechado e com dados preenchidos
1. Vendedor aciona a função "Atualizar RD"
2. A API pega os dados da mensagem na qual o vendedor clicou e confere os dados
3. Clica em "enviar" no formulario de validação
4. A API busca o id da organização do cliente no banco de dados e envia os dados para o RD


