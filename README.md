# Moodle ABNTOnline Webservice Authentication Plugin

Moodle plugin to authenticate users against ABNTOnline webservice.


### Erro

```
{erro_codigo:2,erro_mensagem:"Senha inválida",pessoa:null}
```

### Autenticado com sucesso: Pessoa Física como Associado

```
{erro_codigo:0,erro_mensagem:'', pessoa:{nome:"José SebastiãoViel",data_cadastro:"2007-11-27",sexo:'M',pessoa_fisica:{conselhos:[{descricao:Técnico,status:Titular},{descricao:Deliberativo,status:Titular}],associado:true},pessoa_juridicas:null}}
```

### Autenticado com sucesso: Pessoa Jurídica com Associado

```
{erro_codigo:0,erro_mensagem:"",pessoa:{nome:"Anita Tereza Dedding",data_cadastro:"2014-06-06",sexo:"",pessoa_fisica:{conselhos:null,associado:false},pessoa_juridicas:[{razao_social:"SINDIMAQ - Sindicato Nacional da Indústria de Máquinas",associado:true,conselhos:[{descricao:Deliberativo,status:Titular},{descricao:Técnico,status:"Titular"}]},{razao_social:"ABIMAQ - Associação Brasileira da Indústria de Maquinas e Equipamentos",associado:true,conselhos:[{descricao:"Deliberativo",status:"Titular"},{
descricao:Técnico,status:Titular}]}]}}
```
