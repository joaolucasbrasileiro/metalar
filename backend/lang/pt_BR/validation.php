<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | As linhas abaixo contem as mensagens padrao usadas pelo validador.
    | Algumas regras possuem versoes diferentes, como regras de tamanho.
    | Voce pode ajustar cada mensagem conforme a necessidade do projeto.
    |
    */

    'accepted' => 'O campo :attribute deve ser aceito.',
    'accepted_if' => 'O campo :attribute deve ser aceito quando :other for :value.',
    'active_url' => 'O campo :attribute deve ser uma URL valida.',
    'after' => 'O campo :attribute deve ser uma data posterior a :date.',
    'after_or_equal' => 'O campo :attribute deve ser uma data posterior ou igual a :date.',
    'alpha' => 'O campo :attribute deve conter apenas letras.',
    'alpha_dash' => 'O campo :attribute deve conter apenas letras, numeros, tracos e sublinhados.',
    'alpha_num' => 'O campo :attribute deve conter apenas letras e numeros.',
    'any_of' => 'O campo :attribute e invalido.',
    'array' => 'O campo :attribute deve ser um array.',
    'ascii' => 'O campo :attribute deve conter apenas caracteres alfanumericos e simbolos de byte unico.',
    'before' => 'O campo :attribute deve ser uma data anterior a :date.',
    'before_or_equal' => 'O campo :attribute deve ser uma data anterior ou igual a :date.',
    'between' => [
        'array' => 'O campo :attribute deve ter entre :min e :max itens.',
        'file' => 'O campo :attribute deve ter entre :min e :max quilobytes.',
        'numeric' => 'O campo :attribute deve estar entre :min e :max.',
        'string' => 'O campo :attribute deve ter entre :min e :max caracteres.',
    ],
    'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
    'can' => 'O campo :attribute contem um valor nao autorizado.',
    'confirmed' => 'A confirmacao do campo :attribute nao confere.',
    'contains' => 'O campo :attribute nao contem um valor obrigatorio.',
    'current_password' => 'A senha esta incorreta.',
    'date' => 'O campo :attribute deve ser uma data valida.',
    'date_equals' => 'O campo :attribute deve ser uma data igual a :date.',
    'date_format' => 'O campo :attribute deve corresponder ao formato :format.',
    'decimal' => 'O campo :attribute deve ter :decimal casas decimais.',
    'declined' => 'O campo :attribute deve ser recusado.',
    'declined_if' => 'O campo :attribute deve ser recusado quando :other for :value.',
    'different' => 'Os campos :attribute e :other devem ser diferentes.',
    'digits' => 'O campo :attribute deve ter :digits digitos.',
    'digits_between' => 'O campo :attribute deve ter entre :min e :max digitos.',
    'dimensions' => 'O campo :attribute possui dimensoes de imagem invalidas.',
    'distinct' => 'O campo :attribute possui um valor duplicado.',
    'doesnt_contain' => 'O campo :attribute nao deve conter nenhum dos seguintes valores: :values.',
    'doesnt_end_with' => 'O campo :attribute nao deve terminar com um dos seguintes valores: :values.',
    'doesnt_start_with' => 'O campo :attribute nao deve comecar com um dos seguintes valores: :values.',
    'email' => 'O campo :attribute deve ser um endereco de e-mail valido.',
    'encoding' => 'O campo :attribute deve estar codificado em :encoding.',
    'ends_with' => 'O campo :attribute deve terminar com um dos seguintes valores: :values.',
    'enum' => 'O :attribute selecionado e invalido.',
    'exists' => 'O :attribute selecionado e invalido.',
    'extensions' => 'O campo :attribute deve ter uma das seguintes extensoes: :values.',
    'file' => 'O campo :attribute deve ser um arquivo.',
    'filled' => 'O campo :attribute deve ter um valor.',
    'gt' => [
        'array' => 'O campo :attribute deve ter mais de :value itens.',
        'file' => 'O campo :attribute deve ser maior que :value quilobytes.',
        'numeric' => 'O campo :attribute deve ser maior que :value.',
        'string' => 'O campo :attribute deve ter mais de :value caracteres.',
    ],
    'gte' => [
        'array' => 'O campo :attribute deve ter :value itens ou mais.',
        'file' => 'O campo :attribute deve ser maior ou igual a :value quilobytes.',
        'numeric' => 'O campo :attribute deve ser maior ou igual a :value.',
        'string' => 'O campo :attribute deve ter :value caracteres ou mais.',
    ],
    'hex_color' => 'O campo :attribute deve ser uma cor hexadecimal valida.',
    'image' => 'O campo :attribute deve ser uma imagem.',
    'in' => 'O :attribute selecionado e invalido.',
    'in_array' => 'O campo :attribute deve existir em :other.',
    'in_array_keys' => 'O campo :attribute deve conter pelo menos uma das seguintes chaves: :values.',
    'integer' => 'O campo :attribute deve ser um numero inteiro.',
    'ip' => 'O campo :attribute deve ser um endereco IP valido.',
    'ipv4' => 'O campo :attribute deve ser um endereco IPv4 valido.',
    'ipv6' => 'O campo :attribute deve ser um endereco IPv6 valido.',
    'json' => 'O campo :attribute deve ser uma string JSON valida.',
    'list' => 'O campo :attribute deve ser uma lista.',
    'lowercase' => 'O campo :attribute deve estar em letras minusculas.',
    'lt' => [
        'array' => 'O campo :attribute deve ter menos de :value itens.',
        'file' => 'O campo :attribute deve ser menor que :value quilobytes.',
        'numeric' => 'O campo :attribute deve ser menor que :value.',
        'string' => 'O campo :attribute deve ter menos de :value caracteres.',
    ],
    'lte' => [
        'array' => 'O campo :attribute nao deve ter mais que :value itens.',
        'file' => 'O campo :attribute deve ser menor ou igual a :value quilobytes.',
        'numeric' => 'O campo :attribute deve ser menor ou igual a :value.',
        'string' => 'O campo :attribute deve ter no maximo :value caracteres.',
    ],
    'mac_address' => 'O campo :attribute deve ser um endereco MAC valido.',
    'max' => [
        'array' => 'O campo :attribute nao deve ter mais que :max itens.',
        'file' => 'O campo :attribute nao deve ser maior que :max quilobytes.',
        'numeric' => 'O campo :attribute nao deve ser maior que :max.',
        'string' => 'O campo :attribute nao deve ter mais que :max caracteres.',
    ],
    'max_digits' => 'O campo :attribute nao deve ter mais que :max digitos.',
    'mimes' => 'O campo :attribute deve ser um arquivo do tipo: :values.',
    'mimetypes' => 'O campo :attribute deve ser um arquivo do tipo: :values.',
    'min' => [
        'array' => 'O campo :attribute deve ter pelo menos :min itens.',
        'file' => 'O campo :attribute deve ter pelo menos :min quilobytes.',
        'numeric' => 'O campo :attribute deve ser pelo menos :min.',
        'string' => 'O campo :attribute deve ter pelo menos :min caracteres.',
    ],
    'min_digits' => 'O campo :attribute deve ter pelo menos :min digitos.',
    'missing' => 'O campo :attribute deve estar ausente.',
    'missing_if' => 'O campo :attribute deve estar ausente quando :other for :value.',
    'missing_unless' => 'O campo :attribute deve estar ausente, a menos que :other seja :value.',
    'missing_with' => 'O campo :attribute deve estar ausente quando :values estiver presente.',
    'missing_with_all' => 'O campo :attribute deve estar ausente quando :values estiverem presentes.',
    'multiple_of' => 'O campo :attribute deve ser multiplo de :value.',
    'not_in' => 'O :attribute selecionado e invalido.',
    'not_regex' => 'O formato do campo :attribute e invalido.',
    'numeric' => 'O campo :attribute deve ser um numero.',
    'password' => [
        'letters' => 'O campo :attribute deve conter pelo menos uma letra.',
        'mixed' => 'O campo :attribute deve conter pelo menos uma letra maiuscula e uma minuscula.',
        'numbers' => 'O campo :attribute deve conter pelo menos um numero.',
        'symbols' => 'O campo :attribute deve conter pelo menos um simbolo.',
        'uncompromised' => 'O :attribute informado apareceu em um vazamento de dados. Escolha outro :attribute.',
    ],
    'present' => 'O campo :attribute deve estar presente.',
    'present_if' => 'O campo :attribute deve estar presente quando :other for :value.',
    'present_unless' => 'O campo :attribute deve estar presente, a menos que :other seja :value.',
    'present_with' => 'O campo :attribute deve estar presente quando :values estiver presente.',
    'present_with_all' => 'O campo :attribute deve estar presente quando :values estiverem presentes.',
    'prohibited' => 'O campo :attribute e proibido.',
    'prohibited_if' => 'O campo :attribute e proibido quando :other for :value.',
    'prohibited_if_accepted' => 'O campo :attribute e proibido quando :other for aceito.',
    'prohibited_if_declined' => 'O campo :attribute e proibido quando :other for recusado.',
    'prohibited_unless' => 'O campo :attribute e proibido, a menos que :other esteja em :values.',
    'prohibits' => 'O campo :attribute impede que :other esteja presente.',
    'regex' => 'O formato do campo :attribute e invalido.',
    'required' => 'O campo :attribute e obrigatorio.',
    'required_array_keys' => 'O campo :attribute deve conter entradas para: :values.',
    'required_if' => 'O campo :attribute e obrigatorio quando :other for :value.',
    'required_if_accepted' => 'O campo :attribute e obrigatorio quando :other for aceito.',
    'required_if_declined' => 'O campo :attribute e obrigatorio quando :other for recusado.',
    'required_unless' => 'O campo :attribute e obrigatorio, a menos que :other esteja em :values.',
    'required_with' => 'O campo :attribute e obrigatorio quando :values estiver presente.',
    'required_with_all' => 'O campo :attribute e obrigatorio quando :values estiverem presentes.',
    'required_without' => 'O campo :attribute e obrigatorio quando :values nao estiver presente.',
    'required_without_all' => 'O campo :attribute e obrigatorio quando nenhum dos campos :values estiver presente.',
    'same' => 'O campo :attribute deve corresponder a :other.',
    'size' => [
        'array' => 'O campo :attribute deve conter :size itens.',
        'file' => 'O campo :attribute deve ter :size quilobytes.',
        'numeric' => 'O campo :attribute deve ser :size.',
        'string' => 'O campo :attribute deve ter :size caracteres.',
    ],
    'starts_with' => 'O campo :attribute deve comecar com um dos seguintes valores: :values.',
    'string' => 'O campo :attribute deve ser um texto.',
    'timezone' => 'O campo :attribute deve ser um fuso horario valido.',
    'unique' => 'O campo :attribute ja esta em uso.',
    'uploaded' => 'Falha ao enviar o campo :attribute.',
    'uppercase' => 'O campo :attribute deve estar em letras maiusculas.',
    'url' => 'O campo :attribute deve ser uma URL valida.',
    'ulid' => 'O campo :attribute deve ser um ULID valido.',
    'uuid' => 'O campo :attribute deve ser um UUID valido.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Aqui voce pode especificar mensagens personalizadas para atributos usando
    | a convencao "attribute.rule". Isso facilita definir uma mensagem
    | especifica para uma regra de um determinado atributo.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | As linhas abaixo substituem o placeholder :attribute por um nome mais
    | amigavel, como "e-mail" em vez de "email". Isso melhora as mensagens
    | exibidas para o usuario.
    |
    */

    'attributes' => [
        'name' => 'nome',
        'email' => 'e-mail',
        'password' => 'senha',
        'password_confirmation' => 'confirmacao de senha',
        'birthday' => 'data de nascimento',
        'cpf' => 'CPF',
        'phone' => 'telefone',
        'role' => 'perfil',
    ],

];
