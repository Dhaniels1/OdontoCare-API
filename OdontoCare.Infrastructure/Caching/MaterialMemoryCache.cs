using Microsoft.Extensions.Caching.Memory;
using Microsoft.Extensions.Logging;
using OdontoCare.Application.Features.Estoque.DTOs;
using OdontoCare.Application.Features.Estoque.Interfaces;

namespace OdontoCare.Infrastructure.Caching;

public sealed class MaterialMemoryCache(IMemoryCache cache, Microsoft.Extensions.Logging.ILogger<MaterialMemoryCache> logger) : IMaterialCache
{
    private const string Key = "odontocare:materiais:listagem:v1";
    private readonly object gate = new();
    private long geracao;

    public async Task<List<MaterialDto>> ObterOuCarregarAsync(Func<Task<List<MaterialDto>>> carregar)
    {
        while (true)
        {
            long geracaoDaLeitura;
            lock (gate)
            {
                if (cache.TryGetValue(Key, out List<MaterialDto>? dados) && dados != null)
                {
                    logger.LogInformation("CACHE HIT: materiais (origem: memória).");
                    return dados;
                }
                geracaoDaLeitura = geracao;
            }

            logger.LogInformation("CACHE MISS: materiais (origem: banco de dados).");
            // Não mantém o bloqueio durante I/O: gravações podem invalidar imediatamente.
            var lista = await carregar();
            lock (gate)
            {
                // Se houve escrita enquanto a consulta aguardava o banco, descarte
                // a leitura antiga e consulte novamente (ou use a nova geração pronta).
                if (geracaoDaLeitura != geracao) continue;
                cache.Set(Key, lista, new MemoryCacheEntryOptions
                {
                    AbsoluteExpirationRelativeToNow = TimeSpan.FromMinutes(2),
                    SlidingExpiration = TimeSpan.FromSeconds(30),
                    Size = 1
                });
                return lista;
            }
        }
    }
    public void Invalidar()
    {
        // A verificação da geração e a publicação são atômicas com a invalidação.
        lock (gate)
        {
            geracao++;
            cache.Remove(Key);
        }
        logger.LogInformation("CACHE INVALIDADO: materiais.");
    }
}
