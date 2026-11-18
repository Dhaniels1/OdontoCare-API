using System.Linq.Expressions;

namespace OdontoCare.Application.Common.Interfaces;

public interface IEntityRepository<T> where T : class
{
    Task<List<T>> ListarAsync(Expression<Func<T, bool>>? filtro = null);
    Task<T?> BuscarAsync(Expression<Func<T, bool>> filtro);
    Task<bool> ExisteAsync(Expression<Func<T, bool>> filtro);
    void Adicionar(T entity);
    void Remover(T entity);
    Task SalvarAsync();
}
