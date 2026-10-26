
using OdontoCare.Domain.Entities;

namespace OdontoCare.Application.Interfaces;

public interface IConsultaService
{
    Task<List<Consulta>> GetTodasAsync();

    Task<Consulta?> GetPorIdAsync(int id);

    Task<Consulta> CriarAsync(Consulta consulta);

    Task<bool> AtualizarAsync(int id, Consulta consulta);

    Task<bool> ExcluirAsync(int id);
    Task<bool> CancelarAsync(int id);
}
