using System.Linq.Expressions;
namespace OdontoCare.Application.Interfaces;
// Não expõe EF, conexões ou IQueryable às regras da aplicação.
public interface IEntityRepository<T> where T : class
{
    Task<List<T>> ListarAsync(Expression<Func<T, bool>>? filtro = null);
    Task<T?> BuscarAsync(Expression<Func<T, bool>> filtro);
    Task<bool> ExisteAsync(Expression<Func<T, bool>> filtro);
    void Adicionar(T entity);
    void Remover(T entity);
    Task SalvarAsync();
}
public interface IAgendaTransaction : IAsyncDisposable { Task CommitAsync(); }
public interface IAgendaLock
{
    Task<IAgendaTransaction> IniciarAsync();
    Task BloquearAsync(int pacienteId, int usuarioId);
}
public sealed record HorarioClinica(TimeSpan Inicio, TimeSpan Fim);
