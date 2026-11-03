using OdontoCare.Application.DTOs;
using OdontoCare.Application.Interfaces;
using OdontoCare.Domain.Entities;

namespace OdontoCare.Application.Services;

public sealed class EquipamentoService(IEntityRepository<Equipamento> equipamentos) : IEquipamentoService
{
    private static EquipamentoDto Map(Equipamento e) => new(e.IdEquipamento, e.NomeEquipamento, e.Descricao, e.StatusEquipamento);
    public async Task<List<EquipamentoDto>> ListarAsync() => (await equipamentos.ListarAsync()).OrderBy(x => x.NomeEquipamento).Select(Map).ToList();
    public async Task<EquipamentoDto?> BuscarAsync(int id) => (await equipamentos.BuscarAsync(x => x.IdEquipamento == id)) is { } e ? Map(e) : null;
    public async Task<EquipamentoDto> CriarAsync(EquipamentoGravarDto dto)
    {
        var entity = new Equipamento { NomeEquipamento = dto.NomeEquipamento.Trim(), Descricao = dto.Descricao?.Trim(), StatusEquipamento = dto.StatusEquipamento?.Trim() };
        equipamentos.Adicionar(entity);
        await equipamentos.SalvarAsync();
        return Map(entity);
    }
    public async Task<EquipamentoDto?> AtualizarAsync(int id, EquipamentoGravarDto dto)
    {
        var e = await equipamentos.BuscarAsync(x => x.IdEquipamento == id);
        if (e is null) return null;
        e.NomeEquipamento = dto.NomeEquipamento.Trim(); e.Descricao = dto.Descricao?.Trim(); e.StatusEquipamento = dto.StatusEquipamento?.Trim();
        await equipamentos.SalvarAsync();
        return Map(e);
    }
    public async Task<bool> ExcluirAsync(int id)
    {
        var e = await equipamentos.BuscarAsync(x => x.IdEquipamento == id);
        if (e is null) return false;
        equipamentos.Remover(e);
        await equipamentos.SalvarAsync();
        return true;
    }
}
