using Microsoft.Extensions.Caching.Memory;
using Microsoft.Extensions.Logging.Abstractions;
using OdontoCare.Application.DTOs;
using OdontoCare.Infrastructure.Caching;

static List<MaterialDto> Dados(int quantidade) => [new(1, "Material de teste", null, quantidade, 0, "un", false)];
static TaskCompletionSource<List<MaterialDto>> Bloqueio() => new(TaskCreationOptions.RunContinuationsAsynchronously);
static void Exigir(bool condicao, string mensagem)
{
    if (!condicao) throw new InvalidOperationException(mensagem);
}
static MaterialMemoryCache Criar(MemoryCache memoria) => new(memoria, NullLogger<MaterialMemoryCache>.Instance);

// Os sinais controlam a ordem das operações, sem sleeps ou banco de dados.
using (var memoria = new MemoryCache(new MemoryCacheOptions()))
{
    var cache = Criar(memoria);
    var leituraAntiga = Bloqueio();
    var chamadas = 0;
    var pendente = cache.ObterOuCarregarAsync(() => ++chamadas == 1 ? leituraAntiga.Task : Task.FromResult(Dados(2)));
    cache.Invalidar();
    leituraAntiga.SetResult(Dados(1));
    var resposta = await pendente.WaitAsync(TimeSpan.FromSeconds(5));
    var seguinte = await cache.ObterOuCarregarAsync(() => throw new Exception("O dado novo deveria estar no cache."));
    Exigir(resposta[0].Quantidade == 2 && seguinte[0].Quantidade == 2 && chamadas == 2,
        "Leitura anterior à invalidação retornou ou publicou dados antigos.");
    Console.WriteLine("PASS: leitura lenta recarrega após invalidação.");
}

using (var memoria = new MemoryCache(new MemoryCacheOptions()))
{
    var cache = Criar(memoria);
    var leituraAntiga = Bloqueio();
    var pendente = cache.ObterOuCarregarAsync(() => leituraAntiga.Task);
    cache.Invalidar();
    await cache.ObterOuCarregarAsync(() => Task.FromResult(Dados(2)));
    leituraAntiga.SetResult(Dados(1));
    var resposta = await pendente.WaitAsync(TimeSpan.FromSeconds(5));
    var seguinte = await cache.ObterOuCarregarAsync(() => throw new Exception("Cache atual foi perdido."));
    Exigir(resposta[0].Quantidade == 2 && seguinte[0].Quantidade == 2,
        "Leitura antiga sobrescreveu a entrada criada depois da invalidação.");
    Console.WriteLine("PASS: leitura antiga não sobrescreve cache da nova geração.");
}

using (var memoria = new MemoryCache(new MemoryCacheOptions()))
{
    var cache = Criar(memoria);
    var primeira = Bloqueio();
    var segunda = Bloqueio();
    var segundaIniciada = new TaskCompletionSource(TaskCreationOptions.RunContinuationsAsynchronously);
    var chamadas = 0;
    var pendente = cache.ObterOuCarregarAsync(() =>
    {
        chamadas++;
        if (chamadas == 1) return primeira.Task;
        if (chamadas == 2) { segundaIniciada.SetResult(); return segunda.Task; }
        return Task.FromResult(Dados(3));
    });
    cache.Invalidar(); primeira.SetResult(Dados(1));
    await segundaIniciada.Task.WaitAsync(TimeSpan.FromSeconds(5));
    cache.Invalidar(); segunda.SetResult(Dados(2));
    Exigir((await pendente.WaitAsync(TimeSpan.FromSeconds(5)))[0].Quantidade == 3 && chamadas == 3,
        "Invalidações consecutivas não foram respeitadas.");
    Console.WriteLine("PASS: invalidações durante recargas sucessivas.");
}

using (var memoria = new MemoryCache(new MemoryCacheOptions()))
{
    var cache = Criar(memoria);
    var chamadas = 0;
    Task<List<MaterialDto>> Carregar() { chamadas++; return Task.FromResult(Dados(chamadas)); }
    await cache.ObterOuCarregarAsync(Carregar);
    var hits = await Task.WhenAll(Enumerable.Range(0, 20).Select(_ => Task.Run(() => cache.ObterOuCarregarAsync(Carregar))));
    Exigir(chamadas == 1 && hits.All(x => x[0].Quantidade == 1), "HIT deve reutilizar a fonte.");
    cache.Invalidar();
    Exigir((await cache.ObterOuCarregarAsync(Carregar))[0].Quantidade == 2 && chamadas == 2,
        "Invalidação de uma entrada pronta não recarregou a fonte.");
    Console.WriteLine("PASS: HIT paralelo e invalidação de entrada pronta.");
}

using (var memoria = new MemoryCache(new MemoryCacheOptions()))
{
    var cache = Criar(memoria);
    var falhou = false;
    try { await cache.ObterOuCarregarAsync(() => Task.FromException<List<MaterialDto>>(new IOException("Falha simulada"))); }
    catch (IOException) { falhou = true; }
    Exigir(falhou && (await cache.ObterOuCarregarAsync(() => Task.FromResult(Dados(5))))[0].Quantidade == 5,
        "Falha da fonte não deve envenenar ou bloquear o cache.");
    Console.WriteLine("PASS: recuperação após falha da fonte.");
}

Console.WriteLine("5 cenários de regressão do cache aprovados.");
