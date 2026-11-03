using System.Linq.Expressions;
using Microsoft.EntityFrameworkCore;
using Microsoft.EntityFrameworkCore.Storage;
using OdontoCare.Application.Interfaces;
using OdontoCare.Infrastructure.Data;
namespace OdontoCare.Infrastructure.Repositories;
public sealed class EntityRepository<T>(OdontoCareDbContext db) : IEntityRepository<T> where T : class
{
    public Task<List<T>> ListarAsync(Expression<Func<T, bool>>? filtro = null)
    {
        var query = db.Set<T>().AsNoTracking();
        return (filtro is null ? query : query.Where(filtro)).ToListAsync();
    }
    public Task<T?> BuscarAsync(Expression<Func<T, bool>> filtro) => db.Set<T>().FirstOrDefaultAsync(filtro);
    public Task<bool> ExisteAsync(Expression<Func<T, bool>> filtro) => db.Set<T>().AnyAsync(filtro);
    public void Adicionar(T entity) => db.Set<T>().Add(entity);
    public void Remover(T entity) => db.Set<T>().Remove(entity);
    public async Task SalvarAsync() => await db.SaveChangesAsync();
}
public sealed class AgendaLock(OdontoCareDbContext db) : IAgendaLock
{
    public async Task<IAgendaTransaction> IniciarAsync() => new Transaction(
        await db.Database.BeginTransactionAsync(System.Data.IsolationLevel.ReadCommitted));
    public async Task BloquearAsync(int pacienteId, int usuarioId)
    {
        // Ordem fixa em todas as gravações para serializar reservas concorrentes.
        await db.Database.ExecuteSqlInterpolatedAsync($"SELECT idPaciente FROM paciente WHERE idPaciente = {pacienteId} FOR UPDATE");
        await db.Database.ExecuteSqlInterpolatedAsync($"SELECT idUsuario FROM usuario WHERE idUsuario = {usuarioId} FOR UPDATE");
    }
    private sealed class Transaction(IDbContextTransaction transaction) : IAgendaTransaction
    {
        public Task CommitAsync() => transaction.CommitAsync();
        public ValueTask DisposeAsync() => transaction.DisposeAsync();
    }
}
