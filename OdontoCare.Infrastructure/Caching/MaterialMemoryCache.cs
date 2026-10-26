using Microsoft.Extensions.Caching.Memory;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Interfaces;

namespace OdontoCare.Infrastructure.Caching;

public sealed class MaterialMemoryCache(IMemoryCache cache, Microsoft.Extensions.Logging.ILogger<MaterialMemoryCache> logger) : IMaterialCache
{
    private const string Key = "odontocare:materiais:listagem:v1";
    public async Task<List<MaterialDto>> ObterOuCarregarAsync(Func<Task<List<MaterialDto>>> carregar)
    {
        if (cache.TryGetValue(Key, out List<MaterialDto>? dados) && dados != null)
        {
            logger.LogInformation("CACHE HIT: materiais (origem: memória).");
            return dados;
        }
        logger.LogInformation("CACHE MISS: materiais (origem: banco de dados).");
        var lista = await carregar(); // Cache miss -> busca no banco.
        cache.Set(Key, lista, new MemoryCacheEntryOptions
        {
            AbsoluteExpirationRelativeToNow = TimeSpan.FromMinutes(2),
            SlidingExpiration = TimeSpan.FromSeconds(30),
            Size = 1
        });
        return lista;
    }
    public void Invalidar()
    {
        cache.Remove(Key);
        logger.LogInformation("CACHE INVALIDADO: materiais.");
    }
}
