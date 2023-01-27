## Imagem Docker

Uma imagem do Docker é um arquivo que contém todas as configurações e dependências necessárias para rodar um aplicativo em um container. Ela é criada a partir de um arquivo Dockerfile, que especifica as instruções para construir a imagem. As imagens do Docker são armazenadas em repositórios, como o Docker Hub, onde podem ser baixadas e usadas para criar containers.

## Container

Um container é uma instância de uma imagem do Docker que está em execução. Ele contém todos os arquivos e recursos necessários para rodar o aplicativo, incluindo bibliotecas, configurações e variáveis de ambiente. Os containers são isolados uns dos outros, o que significa que eles não interferem uns nos outros e não compartilham recursos do sistema. Isso permite que você execute vários aplicativos em um único host sem conflitos.

## Dockerfile

O arquivo Dockerfile deste repositório está criando uma imagem baseada na imagem PHP 8.0.11-apache. Ele está configurando a pasta raiz do Apache para ser /var/www/html/public; instalando vários pacotes necessários, como ferramentas de compilação, bibliotecas de imagem, localização, ferramentas de otimização de imagem, vim, unzip, git, cur; configurando o xdebug; etc.

Percebe-se que nesse arquivo é especificado o ambiente em que a aplicação funciona, é como um espelho do ambiente linux local, no qual instalaríamos todas essas ferramentas para o funcionamento correto da aplicação

## Docker Compose

O arquivo docker-compose.yml é usado para configurar e gerenciar os containers do Docker em um ambiente de desenvolvimento. Para o projeto deste repositório são definidos quatro serviços: mysql, app, reverse-proxy, e node.

O serviço mysql usa a imagem do MySQL mais recente e define algumas configurações de ambiente, como o nome do banco de dados, usuário e senha. Ele também expõe a porta 23304 para acessar o banco de dados fora do container.

O serviço app é construído a partir do contexto atual e usa labels para habilitar o suporte ao Traefik, que é usado como proxy reverso. Ele expõe a porta 8888 e depende do serviço mysql para funcionar corretamente.

O serviço reverse-proxy usa a imagem do Traefik v2.7, expõe as portas 80, 443 e 3000, e usa volumes para mapear configurações e certificados. Ele também usa o Docker socket do host para detectar automaticamente os outros serviços e configurar rotas para eles.

O serviço node usa a imagem do Node.js e expõe a porta 5173. Ele define uma pasta de trabalho e volumes para acessar arquivos fora do container e executa um script de entrada específico.

Além disso, o arquivo também define volumes e redes que são usados pelos serviços. O volume template-mysql-data é usado para armazenar dados persistentes do MySQL e a rede traefik-network é usada para permitir que os serviços se comuniquem entre si.

## Volumes

No arquivo docker-compose.yml explicado anteriormente, as configurações de volumes foram definidas dentro do bloco services > web > volumes.

Suponha uma configuração um volume que mapeia a pasta local ./code para a pasta /var/www/html dentro do container. Isso permite que você edite os arquivos em sua máquina local e veja as alterações imediatamente no container sem precisar fazer o build da imagem novamente.

Suponha uma configuração que define outro volume que mapeia a pasta local ./config/php para a pasta /usr/local/etc/php dentro do container. Isso permite que você personalize as configurações do PHP no container sem precisar fazer o build da imagem novamente.

Suponha uma configuração que define um volume que mapeia a pasta local ./logs para a pasta /var/log/apache2 dentro do container. Isso permite que você acesse os arquivos de log do Apache diretamente na sua máquina local.

Em resumo, os volumes permitem que você compartilhe pastas entre o container e a máquina host, permitindo editar os arquivos sem precisar fazer o build da imagem novamente e assim garantir uma maior flexibilidade no uso da aplicação.

## Execução

Para executar um arquivo Docker Compose (docker-compose.yml), você precisa ter o Docker Compose instalado em sua máquina. Uma vez instalado, você pode usar o comando "docker-compose up" na pasta onde seu arquivo docker-compose.yml está localizado. Isso vai iniciar todos os contêineres especificados no arquivo.

Para executar um arquivo Dockerfile, você precisa primeiro construir uma imagem a partir dele usando o comando "docker build". Esse comando deve ser executado na pasta onde o arquivo Dockerfile está localizado. Ele irá criar uma imagem com o nome especificado no arquivo ou no comando. Depois de construir uma imagem, você pode usar o comando "docker run" para iniciar um contêiner a partir da imagem criada.

O Docker Compose não executa o comando "docker build" automaticamente. Ele assume que as imagens já estão construídas e disponíveis para serem usadas. No entanto, o Docker Compose possui a opção "build" que permite construir as imagens automaticamente antes de iniciar os contêineres. Ele faz isso lendo o arquivo Dockerfile na pasta especificada no arquivo docker-compose.yml e constrói a imagem antes de iniciar o contêiner.

```
version: "3.8"
services:
  myservice:
    build:
      context: .
      dockerfile: Dockerfile
```

Neste exemplo, o Docker Compose irá ler o arquivo Dockerfile na pasta atual e construir uma imagem antes de iniciar o contêiner "myservice". Nesse caso não é necessário executar o `docker run`.

## Extras

Entendendo o conceito de imagem e container, é possível conhecer também formas de gerenciar os recursos e aprimorar a qualidade do ambiente de execução de uma aplicação. 

Por exemplo, multi-stage build é uma funcionalidade do Docker que permite usar várias etapas (ou estágios) em um único arquivo Dockerfile para construir uma imagem. Cada etapa do build é representada por uma instrução "FROM" no arquivo Dockerfile. Cada etapa pode usar diferentes imagens como base e pode ter suas próprias instruções de build, como COPY, RUN, etc.

O objetivo do multi-stage build é permitir que você use ferramentas de build específicas em cada etapa e, em seguida, use somente a imagem final para executar seu aplicativo, reduzindo assim o tamanho final da imagem e o espaço ocupado no disco.

Por exemplo:

```
# Etapa 1
FROM golang:latest AS build-env
RUN mkdir /app
ADD . /app/
RUN cd /app && go build -o main .

# Etapa 2
FROM alpine:latest
RUN mkdir /app
COPY --from=build-env /app/main /app/
CMD ["/app/main"]
```

Neste exemplo, temos dois estágios de build, o primeiro estágio usa a imagem "golang:latest" e compila o código fonte do projeto, e o segundo estágio usa a imagem "alpine:latest" e copia o executável compilado para essa imagem, e configura o comando para executar o aplicativo.

Ao construir essa imagem, o Docker irá criar duas imagens intermediárias, uma para cada etapa, e no final somente a imagem final será mantida, e essa imagem final é a que você usaria para rodar seu aplicativo.

## Simplificando

Para um teste prático siga as seguintes etapas:

1. Crie uma pasta para o seu projeto e navegue até ela no terminal

2. Crie um arquivo chamado "Dockerfile" na pasta do projeto com o seguinte conteúdo:

```
FROM php:7.4-apache
COPY . /var/www/html/
```

3. Crie um arquivo chamado "docker-compose.yml" na pasta do projeto com o seguinte conteúdo:

```
version: '3.8'
services:
  web:
    build: .
    ports:
      - "80:80"
    volumes:
      - .:/var/www/html/
```

4. No terminal, na pasta do projeto, execute o comando "docker-compose build" para construir a imagem do container.

5. Depois execute o comando "docker-compose up" para iniciar o container.

6. Agora você deve ser capaz de acessar o container rodando em seu navegador digitando "http://localhost"

7. Para parar o container, você pode usar o comando "docker-compose down"

8. Para criar ou editar seus arquivos PHP você pode editar a pasta local do projeto e os arquivos serão automaticamente sincronizados com o container, sem a necessidade de recriá-lo.

Dica: você pode adicionar extensões PHP adicionais para o container adicionando as instruções necessárias no Dockerfile antes da instrução COPY.
