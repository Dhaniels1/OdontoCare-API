using Microsoft.EntityFrameworkCore;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Interfaces;
using OdontoCare.Domain.Entities;
using OdontoCare.Infrastructure.Data;

namespace OdontoCare.Infrastructure.Services;

public sealed class EquipamentoService(OdontoCareDbContext db) : IEquipamentoService
{
    private static EquipamentoDto Map(Equipamento e) => new(e.IdEquipamento, e.NomeEquipamento, e.Descricao, e.StatusEquipamento);
    public async Task<List<EquipamentoDto>> ListarAsync() => (await db.Equipamentos.AsNoTracking().OrderBy(x => x.NomeEquipamento).ToListAsync()).Select(Map).ToList();
    public async Task<EquipamentoDto?> BuscarAsync(int id) => (await db.Equipamentos.FindAsync(id)) is { } e ? Map(e) : null;
    public async Task<EquipamentoDto> CriarAsync(EquipamentoGravarDto dto)
    {
        var entity = new Equipamento { NomeEquipamento = dto.NomeEquipamento.Trim(), Descricao = dto.Descricao?.Trim(), StatusEquipamento = dto.StatusEquipamento?.Trim() };
        db.Equipamentos.Add(entity);
        await db.SaveChangesAsync();
        return Map(entity);
    }
    public async Task<EquipamentoDto?> AtualizarAsync(int id, EquipamentoGravarDto dto)
    {
        var e = await db.Equipamentos.FindAsync(id);
        if (e is null) return null;
        e.NomeEquipamento = dto.NomeEquipamento.Trim(); e.Descricao = dto.Descricao?.Trim(); e.StatusEquipamento = dto.StatusEquipamento?.Trim();
        await db.SaveChangesAsync();
        return Map(e);
    }
    public async Task<bool> ExcluirAsync(int id)
    {
        var e = await db.Equipamentos.FindAsync(id);
        if (e is null) return false;
        db.Equipamentos.Remove(e);
        await db.SaveChangesAsync();
        return true;
    }
}
