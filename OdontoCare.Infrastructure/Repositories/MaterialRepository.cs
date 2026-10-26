using Microsoft.EntityFrameworkCore;
using OdontoCare.Application.Common;
using OdontoCare.Application.Interfaces;
using OdontoCare.Domain.Entities;
using OdontoCare.Infrastructure.Data;

namespace OdontoCare.Infrastructure.Repositories;

public sealed class MaterialRepository(OdontoCareDbContext db) : IMaterialRepository
{
    public Task<List<Material>> ListarAsync() => db.Materiais.AsNoTracking()
        .OrderBy(m => m.NomeMaterial).ToListAsync();
    public Task<Material?> BuscarAsync(int id) => db.Materiais.FirstOrDefaultAsync(m => m.IdMaterial == id);
    public async Task<Material> CriarAsync(Material material)
    {
        db.Materiais.Add(material);
        await db.SaveChangesAsync();
        return material;
    }
    public async Task<bool> AtualizarAsync(Material material)
    {
        await db.SaveChangesAsync();
        return true;
    }
    public async Task<bool> ExcluirAsync(int id)
    {
        var material = await BuscarAsync(id);
        if (material is null) return false;
        if (await db.MovimentacoesEstoque.AnyAsync(x => x.IdMaterial == id))
            throw new BusinessException("Material possui movimentações. A exclusão é bloqueada para preservar o histórico.");
        db.Materiais.Remove(material);
        await db.SaveChangesAsync();
        return true;
    }

    public async Task<Material?> MovimentarAsync(int id, string tipo, int quantidade, int usuario)
    {
        // Bloqueio de escrita com transação para impedir saída concorrente gerar saldo negativo.
        await using var transacao = await db.Database.BeginTransactionAsync(System.Data.IsolationLevel.Serializable);
        var material = await db.Materiais.FirstOrDefaultAsync(m => m.IdMaterial == id);
        if (material is null) return null;
        if (tipo == "Saida")
        {
            if (material.Quantidade < quantidade)
                throw new BusinessException("Estoque insuficiente para realizar a saída.");
            material.Quantidade -= quantidade;
        }
        else if (tipo == "Entrada")
        {
            if (material.Quantidade > int.MaxValue - quantidade)
                throw new BusinessException("Quantidade excede o limite de estoque.", 400);
            material.Quantidade += quantidade;
        }
        else throw new BusinessException("Tipo de movimentação inválido.", 400);
        db.MovimentacoesEstoque.Add(new MovimentacaoEstoque
        {
            IdMaterial = id, Quantidade = quantidade, Tipo = tipo,
            DataUtc = DateTime.UtcNow, IdUsuario = usuario
        });
        await db.SaveChangesAsync();
        await transacao.CommitAsync();
        return material;
    }

    public Task<List<MovimentacaoEstoque>> HistoricoAsync(int materialId) =>
        db.MovimentacoesEstoque.AsNoTracking().Where(x => x.IdMaterial == materialId)
        .OrderByDescending(x => x.DataUtc).ToListAsync();
}
