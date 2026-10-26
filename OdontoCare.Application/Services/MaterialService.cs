using OdontoCare.Application.Common;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Interfaces;
using OdontoCare.Domain.Entities;

namespace OdontoCare.Application.Services;

public sealed class MaterialService(IMaterialRepository repositorio, IMaterialCache cache) : IMaterialService
{
    private static MaterialDto Map(Material m) =>
        new(m.IdMaterial, m.NomeMaterial, m.Descricao, m.Quantidade, m.EstoqueMinimo,
            m.Unidade, m.Quantidade <= m.EstoqueMinimo);

    public async Task<PaginaDto<MaterialDto>> ListarAsync(int pagina, int tamanho, string? busca, bool? estoqueBaixo)
    {
        if (pagina < 1 || tamanho < 1 || tamanho > 100)
            throw new BusinessException("Página deve ser positiva e tamanho entre 1 e 100.", 400);
        var todos = await cache.ObterOuCarregarAsync(async () =>
            (await repositorio.ListarAsync()).Select(Map).ToList());
        IEnumerable<MaterialDto> resultado = todos;
        if (!string.IsNullOrWhiteSpace(busca))
            resultado = resultado.Where(x => x.NomeMaterial.Contains(busca.Trim(), StringComparison.OrdinalIgnoreCase));
        if (estoqueBaixo.HasValue)
            resultado = resultado.Where(x => x.EstoqueBaixo == estoqueBaixo.Value);
        var filtrados = resultado.ToList();
        return new(filtrados.Skip((pagina - 1) * tamanho).Take(tamanho).ToList(),
            pagina, tamanho, filtrados.Count);
    }

    public async Task<MaterialDto?> BuscarAsync(int id) =>
        (await repositorio.BuscarAsync(id)) is { } m ? Map(m) : null;

    public async Task<MaterialDto> CriarAsync(MaterialGravarDto dto)
    {
        var material = await repositorio.CriarAsync(new Material
        {
            NomeMaterial = dto.NomeMaterial.Trim(), Descricao = dto.Descricao?.Trim(),
            EstoqueMinimo = dto.EstoqueMinimo, Unidade = dto.Unidade.Trim(), Quantidade = 0
        });
        cache.Invalidar();
        return Map(material);
    }

    public async Task<MaterialDto?> AtualizarAsync(int id, MaterialGravarDto dto)
    {
        var material = await repositorio.BuscarAsync(id);
        if (material is null) return null;
        material.NomeMaterial = dto.NomeMaterial.Trim();
        material.Descricao = dto.Descricao?.Trim();
        material.EstoqueMinimo = dto.EstoqueMinimo;
        material.Unidade = dto.Unidade.Trim();
        if (!await repositorio.AtualizarAsync(material)) return null;
        cache.Invalidar();
        return Map(material);
    }

    public async Task<bool> ExcluirAsync(int id)
    {
        var ok = await repositorio.ExcluirAsync(id);
        if (ok) cache.Invalidar();
        return ok;
    }

    public async Task<MaterialDto?> MovimentarAsync(int id, string tipo, int quantidade, int usuario)
    {
        if (quantidade <= 0) throw new BusinessException("Quantidade deve ser maior que zero.", 400);
        var atualizado = await repositorio.MovimentarAsync(id, tipo, quantidade, usuario);
        if (atualizado is null) return null;
        cache.Invalidar();
        return Map(atualizado);
    }

    public async Task<IReadOnlyList<MovimentacaoDto>> HistoricoAsync(int materialId) =>
        (await repositorio.HistoricoAsync(materialId))
        .Select(m => new MovimentacaoDto(m.IdMovimentacao, m.IdMaterial, m.Tipo,
            m.Quantidade, m.DataUtc, m.IdUsuario)).ToArray();
}
